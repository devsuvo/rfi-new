<?php

namespace App\Support;

/**
 * Turns a class sheet (resources/reading/*.txt) into test-page tabs.
 *
 * Line syntax:
 *   = Tab name            new tab (one passage + its questions)
 *   @single               this tab has one column only (no questions)
 *   @time 8               this tab has its own 8-minute timer; it locks when the time is up
 *                         (without @time a tab gets 2 minutes per question: 5 questions = 10 min)
 *   @fulltest             whole sheet is one IELTS test: a single 60-minute timer, no per-tab limits
 *   # Title               passage title
 *   _ text                italic passage intro
 *   [A] text              lettered paragraph
 *   text                  passage paragraph (questions column: plain line)
 *   ---                   switch to the questions column
 *   ## Questions 1–5      question heading
 *   > text                instruction
 *   ### text              topic title
 *   @tfng N text          TRUE / FALSE / NOT GIVEN picker
 *   @ynng N text          YES / NO / NOT GIVEN picker
 *   @sel N A-G text       letter picker (A-G, i-viii or a comma list)
 *   @mcq N stem           multiple choice; "- option" lines follow
 *   @mcq2 N,M stem        choose TWO; "- option" lines follow
 *   @box Title            boxed list; "* item" lines follow
 *   @img path             picture (public/images/reading/...)
 *   @pin N left top [w]   after @img: answer box N on the picture (left / vertical centre / width in %)
 *   @table … @end         table; cells split by "|", "!" starts a header row
 *   [[N]]                 answer box inside any line; [[N:A-G]] is a letter picker
 */
class ReadingSheet
{
    private array $panes = [];
    private int $tab = 0;
    private string $col = 'passage';
    private ?array $mcq = null;
    private ?array $box = null;
    private ?array $table = null;
    private ?array $img = null;

    public static function load(string $path): array
    {
        return (new self)->parse(file_get_contents($path));
    }

    public function parse(string $text): array
    {
        foreach (preg_split('/\R/', $text) as $raw) {
            $line = trim($raw);
            if ($line === '' || str_starts_with($line, '//')) {
                continue;
            }
            $this->line($line);
        }
        $this->flush();

        // Each exercise gets 2 minutes per question unless it sets @time, or the sheet is a @fulltest.
        foreach ($this->panes as &$pane) {
            preg_match_all('/name="(p\d+_q[\d_]+)/', $pane['passage'].$pane['questions'], $m);
            $pane['count'] = array_sum(array_map(fn ($f) => substr_count($f, '_'), array_unique($m[1])));
            if (! $this->fullTest && empty($pane['minutes']) && $pane['count'] > 0) {
                $pane['minutes'] = $pane['count'] * self::MINUTES_PER_QUESTION;
            }
        }

        return $this->panes;
    }

    public const MINUTES_PER_QUESTION = 2;

    private bool $fullTest = false;

    private function line(string $l): void
    {
        if ($this->table !== null) {
            if ($l === '@end') {
                $this->emit($this->renderTable());
                $this->table = null;
            } else {
                $this->table[] = $l;
            }
            return;
        }
        if ($this->mcq !== null && str_starts_with($l, '- ')) {
            $this->mcq['opts'][] = substr($l, 2);
            return;
        }
        if ($this->img !== null && preg_match('/^@pin (\d+) ([\d.]+) ([\d.]+)(?: ([\d.]+))?$/', $l, $m)) {
            $this->img['pins'][] = $m;
            return;
        }
        if ($this->box !== null && str_starts_with($l, '* ')) {
            $this->box['items'][] = substr($l, 2);
            return;
        }
        $this->flush();

        if (str_starts_with($l, '= ')) {
            $this->panes[] = ['name' => substr($l, 2), 'passage' => '', 'questions' => '', 'single' => false];
            $this->tab = count($this->panes);
            $this->col = 'passage';
            return;
        }
        if ($l === '@fulltest') { $this->fullTest = true; return; }
        if ($l === '@single') { $this->panes[$this->tab - 1]['single'] = true; return; }
        if (preg_match('/^@time (\d+)$/', $l, $m)) { $this->panes[$this->tab - 1]['minutes'] = (int) $m[1]; return; }
        if ($l === '---') { $this->col = 'questions'; return; }
        if ($l === '@table') { $this->table = []; return; }

        if (preg_match('/^@(tfng|ynng) (\d+) (.+)$/', $l, $m)) {
            $opts = $m[1] === 'tfng' ? ['TRUE', 'FALSE', 'NOT GIVEN'] : ['YES', 'NO', 'NOT GIVEN'];
            $this->emit($this->judgement($m[2], $m[3], $opts));
            return;
        }
        if (preg_match('/^@sel (\d+) (\S+) (.+)$/', $l, $m)) {
            $this->emit($this->statement($m[1], $m[3], self::options($m[2])));
            return;
        }
        if (preg_match('/^@(mcq2?) ([\d,]+) (.+)$/', $l, $m)) {
            $this->mcq = ['two' => $m[1] === 'mcq2', 'nums' => $m[2], 'stem' => $m[3], 'opts' => []];
            return;
        }
        if (preg_match('/^@box ?(.*)$/', $l, $m)) {
            $this->box = ['title' => $m[1], 'items' => []];
            return;
        }
        if (preg_match('/^@img (.+)$/', $l, $m)) {
            $this->img = ['src' => $m[1], 'pins' => []];
            return;
        }
        if (str_starts_with($l, '### ')) { $this->emit('<h5 class="topic-title">'.$this->inline(substr($l, 4)).'</h5>'); return; }
        if (str_starts_with($l, '## ')) { $this->emit('<h4>'.$this->inline(substr($l, 3)).'</h4>'); return; }
        if (str_starts_with($l, '# ')) { $this->emit('<h3>'.$this->inline(substr($l, 2)).'</h3>'); return; }
        if (str_starts_with($l, '> ')) { $this->emit('<p class="t-instr">'.$this->inline(substr($l, 2)).'</p>'); return; }
        if (str_starts_with($l, '_ ')) { $this->emit('<p class="p-intro">'.$this->inline(substr($l, 2)).'</p>'); return; }
        if (preg_match('/^\[([A-Z])\] (.+)$/', $l, $m)) {
            $this->emit('<p><span class="para-label">'.$m[1].'</span>'.$this->inline($m[2]).'</p>');
            return;
        }
        $cls = str_contains($l, '[[') ? ' class="t-line"' : '';
        $this->emit("<p{$cls}>".$this->inline($l).'</p>');
    }

    private function flush(): void
    {
        if ($this->img) {
            $i = $this->img;
            $this->img = null;
            $img = '<img src="'.e(asset($i['src'])).'" alt="">';
            $svg = $i['pins'] && str_ends_with($i['src'], '.svg') ? @file_get_contents(public_path($i['src'])) : false;
            if ($svg && preg_match('/viewBox="[\d.]+ [\d.]+ ([\d.]+) ([\d.]+)"/', $svg, $vb)) {
                // answer boxes drawn inside the SVG itself, so they always sit exactly on the blanks
                // (@pin N left top width: % of the picture; top is the box's centre line)
                [$w, $h] = [(float) $vb[1], (float) $vb[2]];
                $boxH = round(max(28, $w / 24)); // wide pictures shrink more on screen, so give them taller boxes
                $boxes = '';
                foreach ($i['pins'] as $p) {
                    $x = $w * $p[2] / 100;
                    $y = $h * $p[3] / 100 - $boxH / 2;
                    $bw = $w * ($p[4] ?? 22) / 100;
                    $boxes .= '<foreignObject x="'.round($x, 1).'" y="'.round($y, 1).'" width="'.round($bw, 1).'" height="'.$boxH.'">'
                        .'<input xmlns="http://www.w3.org/1999/xhtml" class="t-input img-pin" style="font-size:'.round($boxH * .5).'px" name="'.$this->name($p[1]).'" placeholder="'.$p[1].'" autocomplete="off">'
                        .'</foreignObject>';
                }
                // several SVGs share one page (hidden tabs too), so give each its own ids for markers / gradients
                $pre = 'svg'.$this->tab.'-'.substr(md5($i['src']), 0, 6).'-';
                $svg = preg_replace(['/\bid="([^"]+)"/', '/url\(#([^)]+)\)/', '/href="#([^"]+)"/'], ['id="'.$pre.'$1"', 'url(#'.$pre.'$1)', 'href="#'.$pre.'$1"'], $svg);
                $img = '<div class="pin-wrap">'.str_replace('</svg>', $boxes.'</svg>', $svg).'</div>';
            } elseif ($i['pins']) {
                foreach ($i['pins'] as $p) {
                    $img .= '<input class="t-input img-pin img-pin-abs" name="'.$this->name($p[1]).'" placeholder="'.$p[1].'" autocomplete="off"'
                        .' style="left:'.$p[2].'%;top:'.$p[3].'%;width:'.($p[4] ?? 22).'%">';
                }
                $img = '<div class="pin-wrap">'.$img.'</div>';
            }
            $this->emit('<figure class="q-figure">'.$img.'</figure>');
        }
        if ($this->mcq) {
            $m = $this->mcq;
            $this->mcq = null;
            $nums = explode(',', $m['nums']);
            $name = $this->name($nums[0]).($m['two'] ? '_'.implode('_', array_slice($nums, 1)).'[]' : '');
            $label = implode('–', [$nums[0], end($nums)]);
            $html = '<div class="mc-q"'.($m['two'] ? ' data-max="2"' : '').'><div class="mc-stem"><span class="qn">'.($m['two'] ? $label : $nums[0]).'</span><span>'.$this->inline($m['stem']).'</span></div>';
            foreach ($m['opts'] as $i => $o) {
                $type = $m['two'] ? 'checkbox' : 'radio';
                $html .= '<label><input type="'.$type.'" name="'.$name.'" value="'.chr(65 + $i).'"><span>'.$this->inline(preg_replace('/^[A-Z]\.\s*/', '', $o)).'</span></label>';
            }
            $this->emit('<div class="mc-tab">'.$html.'</div></div>');
        }
        if ($this->box) {
            $b = $this->box;
            $this->box = null;
            // Short word lists (e.g. summary options A–L) are laid out across in columns; long ones stay one per line.
            $texts = array_map(fn ($it) => preg_replace('/^\S+?[.)] /', '', $it), $b['items']);
            $wide = count($texts) >= 6 && max(array_map('mb_strlen', $texts) ?: [0]) <= 32;
            $html = '<div class="opt-box'.($wide ? ' wide' : '').'">'.($b['title'] !== '' ? '<div class="opt-title">'.$this->inline($b['title']).'</div>' : '').'<ul>';
            foreach ($b['items'] as $it) {
                $html .= preg_match('/^(\S+?)[.)] (.+)$/', $it, $m)
                    ? '<li><b>'.e($m[1]).'</b>'.$this->inline($m[2]).'</li>'
                    : '<li>'.$this->inline($it).'</li>';
            }
            $this->emit($html.'</ul></div>');
        }
    }

    /** One TRUE/FALSE/NOT GIVEN (or YES/NO/NOT GIVEN) question: a card with a radio per answer. */
    private function judgement(string $n, string $text, array $opts): string
    {
        $html = '<div class="mc-tab tf-tab"><div class="mc-q tf-q"><div class="mc-stem"><span class="qn">'.$n.'</span><span>'.$this->inline($text).'</span></div>';
        foreach ($opts as $o) {
            $html .= '<label><input type="radio" name="'.$this->name($n).'" value="'.e($o).'">'.e($o).'</label>';
        }

        return $html.'</div></div>';
    }

    private function statement(string $n, string $text, array $opts): string
    {
        // the question number shows inside the dropdown, so no number box before the text
        return '<div class="q-row"><span class="q-text">'.$this->inline($text).'</span>'.$this->select($n, $opts).'</div>';
    }

    private function select(string $n, array $opts): string
    {
        $html = '<select class="sel-answer" name="'.$this->name($n).'"><option value="" hidden>'.$n.'</option>';
        foreach ($opts as $o) {
            $html .= '<option>'.e($o).'</option>';
        }
        return $html.'</select>';
    }

    private function renderTable(): string
    {
        $html = '<div class="q-table-wrap"><table class="q-table">';
        foreach ($this->table as $row) {
            $head = str_starts_with($row, '!');
            $tag = $head ? 'th' : 'td';
            $html .= '<tr>';
            foreach (explode('|', ltrim($row, '!')) as $cell) {
                $html .= "<{$tag}>".$this->inline(trim($cell))."</{$tag}>";
            }
            $html .= '</tr>';
        }
        return $html.'</table></div>';
    }

    private function emit(string $html): void
    {
        if (! $this->panes) {
            $this->panes[] = ['name' => 'Part 1', 'passage' => '', 'questions' => '', 'single' => false];
            $this->tab = 1;
        }
        $this->panes[$this->tab - 1][$this->col] .= $html."\n";
    }

    private function name(string $n): string
    {
        return 'p'.$this->tab.'_q'.$n;
    }

    private function inline(string $s): string
    {
        // "**8.** What job …? [[8]]": the number already shows inside the answer box, so drop the one in front
        $s = preg_replace('/^\*\*(\d+)\.?\*\*\s*(?=.*\[\[\1(?::[^\]]*)?\]\])/', '', $s);
        $s = e($s);
        $s = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $s);
        $s = preg_replace('/(?<![\w\/])_(.+?)_(?![\w])/', '<em>$1</em>', $s);

        return preg_replace_callback('/\[\[(\d+)(?::([^\]]+))?\]\]/', function ($m) {
            return isset($m[2])
                ? $this->select($m[1], self::options($m[2]))
                : '<input class="t-input" name="'.$this->name($m[1]).'" placeholder="'.$m[1].'" autocomplete="off">';
        }, $s);
    }

    public static function options(string $spec): array
    {
        if (str_contains($spec, ',')) {
            return explode(',', $spec);
        }
        if (preg_match('/^([A-Z])-([A-Z])$/', $spec, $m)) {
            return range($m[1], $m[2]);
        }
        $roman = ['i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix', 'x', 'xi', 'xii'];
        if (preg_match('/^i-([ivx]+)$/', $spec, $m)) {
            return array_slice($roman, 0, array_search($m[1], $roman) + 1);
        }
        if (preg_match('/^a-([a-z])$/', $spec, $m)) {
            return range('a', $m[1]);
        }
        return [$spec];
    }
}
