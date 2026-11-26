<?php

namespace App\Support;

/**
 * Marks reading answers against an answer key.
 *
 * Key syntax (case-insensitive, extra spaces ignored):
 *   £50 deposit / deposit of £50   " / " (spaces around) = alternative answers
 *   section/area                   "/" between words     = either word
 *   Park View (Hotel), 4(th)       "( )"                 = optional part
 *   B,D                            choose-TWO questions, any order
 */
class Grader
{
    /** @return array{score: int, total: int, detail: array<string, bool>} */
    public static function grade(array $key, array $given): array
    {
        $score = $total = 0;
        $detail = [];
        foreach ($key as $name => $answer) {
            if (trim((string) $answer) === '') {
                continue;
            }
            $value = $given[$name] ?? '';
            if (is_array($value) || str_contains($name, '_q') && substr_count($name, '_') > 1) {
                $want = self::set($answer);
                $got = self::set(is_array($value) ? implode(',', $value) : $value);
                $marks = substr_count($name, '_');
                $hits = count(array_intersect($want, $got));
                $hits = count($got) > count($want) ? max(0, $hits - (count($got) - count($want))) : $hits;
                $score += $hits;
                $total += $marks;
                $detail[$name] = $hits === $marks;
                continue;
            }
            $ok = self::matches((string) $answer, (string) $value);
            $score += $ok ? 1 : 0;
            $total++;
            $detail[$name] = $ok;
        }

        return compact('score', 'total', 'detail');
    }

    public static function matches(string $key, string $value): bool
    {
        $value = self::clean($value);
        if ($value === '') {
            return false;
        }
        foreach (preg_split('/\s+\/\s+/', trim($key)) as $alt) {
            if (preg_match('/^'.self::pattern(self::clean($alt)).'$/iu', $value)) {
                return true;
            }
        }

        return false;
    }

    private static function pattern(string $alt): string
    {
        $out = '';
        $parts = preg_split('/(\s*\([^)]*\))/u', $alt, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        foreach ($parts as $part) {
            if (preg_match('/^(\s*)\(([^)]*)\)$/u', $part, $m)) {
                $out .= '(?:'.($m[1] !== '' ? '\s*' : '').self::words(trim($m[2])).')?';
            } else {
                $out .= self::words($part);
            }
        }

        return $out;
    }

    private static function words(string $s): string
    {
        $tokens = preg_split('/(\s+)/u', $s, -1, PREG_SPLIT_DELIM_CAPTURE);

        return implode('', array_map(function ($t) {
            if (trim($t) === '') {
                return $t === '' ? '' : '\s*';
            }
            $opts = array_map(fn ($w) => preg_quote($w, '/'), explode('/', $t));

            return count($opts) > 1 ? '(?:'.implode('|', $opts).')' : $opts[0];
        }, $tokens));
    }

    private static function clean(string $s): string
    {
        $s = str_replace(['’', '‘', '–', '—'], ["'", "'", '-', '-'], $s);

        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $s)));
    }

    private static function set(string $s): array
    {
        $items = array_filter(array_map(fn ($x) => self::clean($x), preg_split('/[,\s]+/', $s)));
        sort($items);

        return array_values(array_unique($items));
    }

    /** IELTS Academic Reading raw score (out of 40) to band. */
    public static function band(int $score, int $total): ?float
    {
        if ($total < 1) {
            return null;
        }
        $raw = (int) round($score / $total * 40);
        foreach ([39 => 9, 37 => 8.5, 35 => 8, 33 => 7.5, 30 => 7, 27 => 6.5, 23 => 6, 19 => 5.5, 15 => 5, 13 => 4.5, 10 => 4, 8 => 3.5, 6 => 3, 4 => 2.5] as $min => $band) {
            if ($raw >= $min) {
                return $band;
            }
        }

        return $raw > 0 ? 2.0 : 0.0;
    }
}
