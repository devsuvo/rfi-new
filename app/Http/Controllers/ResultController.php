<?php

namespace App\Http\Controllers;

use App\Models\AnswerKey;
use App\Models\Result;
use App\Support\Grader;
use App\Support\Tests;
use Illuminate\Http\Request;

/** Receives a finished test from the student test page. */
class ResultController extends Controller
{
    public function store(Request $request, string $module, string $slug)
    {
        abort_unless(Tests::exists($module, $slug), 404);
        abort_unless(\App\Support\Access::canOpen($request->user(), $module, $slug), 403, 'This class is locked.');

        return $this->save($request, $module, $slug, 'class');
    }

    /** Homework of a reading class, handed in from its own page. */
    public function homework(Request $request, string $slug)
    {
        abort_unless(Tests::exists('reading', $slug), 404);
        $user = $request->user();
        $hw = \App\Support\Access::homeworkFor($user, 'reading', $slug);
        $part = $request->filled('_part') ? (int) $request->input('_part') : null;
        $store = true;
        if (! $user->isStaff()) {
            abort_unless($hw, 403, 'This homework has not been set for your batch.');
            // Homework is handed in one exercise at a time. An exercise already handed in can be practised
            // again: it is marked, but the first try stays the one on record.
            if (\App\Support\Access::homeworkDone($user, 'reading', $slug)
                || ($part && in_array($part, \App\Support\Access::homeworkPartsDone($user, 'reading', $slug), true))) {
                $store = false;
            }
        }

        // A student who started before the deadline can still hand in; it is marked late.
        return $this->save($request, 'reading', $slug, 'homework', (bool) $hw?->overdue(), $store);
    }

    private function save(Request $request, string $module, string $slug, string $kind, bool $late = false, bool $store = true)
    {
        $answers = $request->except(['_token', '_part']);
        // "Finish exercise": only one exercise (tab) of a class is handed in
        $onlyPart = $request->filled('_part') ? (int) $request->input('_part') : null;
        $result = new Result([
            'user_id' => $request->user()->id,
            'module' => $module,
            'test_slug' => $slug,
            'kind' => $kind,
            'part' => $kind === 'homework' ? $onlyPart : null,
            'late' => $late,
            'answers' => $answers,
        ]);

        $out = ['saved' => true];
        $key = AnswerKey::where('module', $module)->where('test_slug', $slug)->first();
        if ($module === 'reading' && $key) {
            // mark only the questions of the part handed in (class work or homework)
            $names = Tests::readingFieldNames($slug, $kind === 'homework');
            if ($onlyPart) {
                $names = array_values(array_filter($names, fn ($n) => str_starts_with($n, 'p'.$onlyPart.'_')));
            }
            $part = array_intersect_key($key->answers, array_flip($names));
            $g = Grader::grade($part, $answers);
            $result->fill(['score' => $g['score'], 'total' => $g['total'], 'band' => Grader::band($g['score'], $g['total'])]);
            $out += ['score' => $g['score'], 'total' => $g['total'], 'band' => $result->band];

            // one row per question for the result popup: your answer vs the correct answer
            $rows = [];
            foreach (Tests::readingFields($slug) as $group) {
                foreach ($group['fields'] as $f) {
                    if (! array_key_exists($f['name'], $g['detail'])) {
                        continue;
                    }
                    $v = $answers[$f['name']] ?? '';
                    $rows[] = [
                        'name' => $f['name'],
                        'q' => $f['label'],
                        'given' => is_array($v) ? implode(', ', $v) : (string) $v,
                        'correct' => (string) $part[$f['name']],
                        'ok' => $g['detail'][$f['name']],
                    ];
                }
            }
            $out['rows'] = $rows;
        }
        if (! $store) {
            return response()->json($out + ['practice' => true]);
        }
        $result->save();
        if ($module === 'reading' && $result->total) {
            $out['result_url'] = route('my.results.show', $result);
        }

        // Homework: no "submitted successfully" message afterwards (the card already shows "Submitted").
        if ($kind !== 'homework') {
            session()->flash('submitted', ucfirst($module).' test submitted successfully!'
                .(isset($out['total']) && $out['total'] ? ' Score: '.$out['score'].' / '.$out['total'].($out['band'] !== null ? ' · Band '.$out['band'] : '') : ''));
        }

        return response()->json($out);
    }
}
