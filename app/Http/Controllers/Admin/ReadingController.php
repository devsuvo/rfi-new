<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerKey;
use App\Models\Result;
use App\Support\Access;
use App\Support\Grader;
use App\Support\Tests;
use Illuminate\Http\Request;

class ReadingController extends Controller
{
    public function answers(Request $request)
    {
        $tests = Tests::all()['reading'];
        $slug = $request->query('test', array_key_first($tests));
        abort_unless(isset($tests[$slug]), 404);
        $key = AnswerKey::firstOrNew(
            ['module' => 'reading', 'test_slug' => $slug],
            ['test_name' => str_replace('-', '', $slug).'_reading', 'answers' => []]
        );

        return view('admin.reading.answers', [
            'tests' => $tests,
            'slug' => $slug,
            'key' => $key,
            'saved' => AnswerKey::where('module', 'reading')->pluck('test_slug')->all(),
            'groups' => Tests::readingFields($slug),
            'build' => $request->boolean('build') || ! $key->exists,
        ]);
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'test_slug' => 'required|string',
            'test_name' => 'required|string|max:120',
            'answers' => 'array',
            'answers.*' => 'nullable|string|max:255',
        ]);
        abort_unless(Tests::exists('reading', $data['test_slug']), 404);
        $answers = array_filter($data['answers'] ?? [], fn ($v) => trim((string) $v) !== '');
        $key = AnswerKey::updateOrCreate(
            ['module' => 'reading', 'test_slug' => $data['test_slug']],
            ['test_name' => $data['test_name'], 'answers' => $answers]
        );

        if ($request->boolean('regrade')) {
            Result::where('module', 'reading')->where('test_slug', $key->test_slug)->each(function ($r) use ($answers) {
                $g = Grader::grade($answers, $r->answers);
                $r->update(['score' => $g['score'], 'total' => $g['total'], 'band' => Grader::band($g['score'], $g['total'])]);
            });
        }

        return redirect()->route('admin.reading.answers', ['test' => $key->test_slug])
            ->with('ok', 'Answers saved ('.count($answers).').');
    }

    public function results(Request $request)
    {
        $q = Access::scopeResults(Result::with('user')->where('module', 'reading')->latest(), $request->user());
        if ($b = $request->query('batch')) {
            $q->whereHas('user', fn ($u) => $u->where('batch', $b));
        }
        if ($t = $request->query('test')) {
            $q->where('test_slug', $t);
        }
        if ($s = $request->query('q')) {
            $q->whereHas('user', fn ($u) => $u->where(fn ($w) => $w->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")));
        }

        return view('admin.reading.results', [
            'results' => $q->paginate(20)->withQueryString(),
            'tests' => Tests::all()['reading'],
            'batches' => Access::batchesFor($request->user()),
        ]);
    }

    public function show(Request $request, Result $result)
    {
        abort_unless($result->module === 'reading', 404);
        abort_unless(Access::canSeeResult($request->user(), $result), 403);
        $key = AnswerKey::where('module', 'reading')->where('test_slug', $result->test_slug)->first();

        return view('admin.reading.show', [
            'result' => $result,
            'key' => $key?->answers ?? [],
            'detail' => $key ? Grader::grade($key->answers, $result->answers)['detail'] : [],
            'groups' => Tests::readingFields($result->test_slug),
        ]);
    }

    public function destroy(Result $result)
    {
        $result->delete();

        return back()->with('ok', 'Result deleted.');
    }
}
