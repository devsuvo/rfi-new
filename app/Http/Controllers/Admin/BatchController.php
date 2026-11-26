<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassUnlock;
use App\Models\Result;
use App\Models\User;
use App\Support\Access;
use App\Support\Tests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Batches: student list per batch and class lock / unlock (teacher: own batches, admin: all). */
class BatchController extends Controller
{
    public function index(Request $request)
    {
        $batches = Access::batchesFor($request->user());
        $students = User::where('role', 'student')->selectRaw('batch, COUNT(*) n')->groupBy('batch')->pluck('n', 'batch');
        $unlocked = ClassUnlock::selectRaw('batch, COUNT(*) n')->groupBy('batch')->pluck('n', 'batch');
        $teachers = \App\Models\TeacherBatch::with('teacher')->get()->groupBy('batch');

        return view('admin.batches.index', compact('batches', 'students', 'unlocked', 'teachers') + [
            'totalClasses' => collect(Tests::all())->flatten()->count(),
        ]);
    }

    public function show(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $students = User::where('role', 'student')->where('batch', $batch)->orderBy('name')
            ->withCount('results')->get();
        $done = Result::whereIn('user_id', $students->pluck('id'))->get()
            ->groupBy(fn ($r) => $r->module.':'.$r->test_slug)->map->count();

        return view('admin.batches.show', [
            'batch' => $batch,
            'students' => $students,
            'tests' => Tests::all(),
            'unlocked' => Access::unlockedFor($batch),
            'shown' => ClassUnlock::where('batch', $batch)->where('module', 'reading')->whereNotNull('answers_shown_at')->pluck('test_slug')->all(),
            'keys' => \App\Models\AnswerKey::where('module', 'reading')->pluck('test_slug')->all(),
            // time parts per class: module:slug => [part => [name, default, minutes]]
            'times' => collect(Tests::all())->flatMap(fn ($list, $module) => collect($list)->mapWithKeys(function ($t, $slug) use ($module, $batch) {
                $over = \App\Support\Timing::overrides($batch, $module, $slug);
                $parts = collect(\App\Support\Timing::parts($module, $slug))->map(fn ($p, $part) => $p + [
                    'default' => $p['minutes'],
                    'minutes' => $over[$part] ?? $p['minutes'],
                ])->all();

                return ["$module:$slug" => $parts];
            }))->all(),
            'done' => $done,
        ]);
    }

    public function toggle(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $data = $request->validate([
            'module' => ['required', Rule::in(Tests::MODULES)],
            'test_slug' => 'required|string',
            'unlocked' => 'required|boolean',
        ]);
        abort_unless(Tests::exists($data['module'], $data['test_slug']), 404);

        $where = ['batch' => $batch, 'module' => $data['module'], 'test_slug' => $data['test_slug']];
        if ($data['unlocked']) {
            ClassUnlock::firstOrCreate($where, ['teacher_id' => $request->user()->id]);
        } else {
            ClassUnlock::where($where)->delete();
        }

        if ($request->expectsJson()) {
            return response()->json(['unlocked' => (bool) $data['unlocked']]);
        }

        return back()->with('ok', Tests::title($data['module'], $data['test_slug']).($data['unlocked'] ? ' unlocked' : ' locked').' for '.$batch.'.');
    }

    /** Show / hide a reading class's correct answers to every student of the batch (live on their screens). */
    public function answers(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $data = $request->validate(['test_slug' => 'required|string', 'shown' => 'required|boolean']);
        abort_unless(Tests::exists('reading', $data['test_slug']), 404);
        abort_unless(\App\Models\AnswerKey::where('module', 'reading')->where('test_slug', $data['test_slug'])->exists(), 422, 'No answer key saved for this class yet.');

        $row = ClassUnlock::firstOrCreate(
            ['batch' => $batch, 'module' => 'reading', 'test_slug' => $data['test_slug']],
            ['teacher_id' => $request->user()->id]
        ); // showing answers also unlocks the class
        $row->update(['answers_shown_at' => $data['shown'] ? now() : null]);

        return response()->json(['shown' => (bool) $data['shown'], 'unlocked' => true]);
    }

    /** Teacher sets this batch's minutes for each part of a test. Same as the default = no override. */
    public function times(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $data = $request->validate([
            'module' => ['required', Rule::in(Tests::MODULES)],
            'test_slug' => 'required|string',
            'minutes' => 'array',
            'minutes.*' => 'nullable|integer|min:1|max:300',
            'reset' => 'nullable|boolean',
        ]);
        abort_unless(Tests::exists($data['module'], $data['test_slug']), 404);

        $where = ['batch' => $batch, 'module' => $data['module'], 'test_slug' => $data['test_slug']];
        foreach (\App\Support\Timing::parts($data['module'], $data['test_slug']) as $part => $p) {
            $min = $request->boolean('reset') ? null : ($data['minutes'][$part] ?? null);
            if (! $min || (int) $min === $p['minutes']) {
                \App\Models\TestTime::where($where + ['part' => $part])->delete();
            } else {
                \App\Models\TestTime::updateOrCreate($where + ['part' => (string) $part], ['minutes' => (int) $min, 'teacher_id' => $request->user()->id]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['times' => (object) \App\Support\Timing::forBatch($batch, $data['module'], $data['test_slug'])]);
        }

        return back()->with('ok', 'Time saved for '.Tests::title($data['module'], $data['test_slug']).' ('.$batch.'). Students already in the test get the change within 10 seconds.');
    }

    /** Set (or take back) a class's homework for every student of the batch. */
    public function homework(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $data = $request->validate([
            'module' => ['required', Rule::in(Tests::MODULES)],
            'test_slug' => 'required|string',
            'assigned' => 'required|boolean',
            'due_at' => 'nullable|date',
            'note' => 'nullable|string|max:500',
        ]);
        abort_unless(Tests::exists($data['module'], $data['test_slug']), 404);
        abort_unless(Tests::homeworkCount($data['module'], $data['test_slug']) > 0, 422, 'This class has no homework.');

        $where = ['batch' => $batch, 'module' => $data['module'], 'test_slug' => $data['test_slug']];
        if (! $data['assigned']) {
            \App\Models\HomeworkAssignment::where($where)->delete();

            return response()->json(['assigned' => false]);
        }
        $hw = \App\Models\HomeworkAssignment::updateOrCreate($where, [
            'due_at' => $data['due_at'] ?? null,
            'note' => $data['note'] ?? null,
            'teacher_id' => $request->user()->id,
        ]);

        return response()->json([
            'assigned' => true,
            'due_at' => $hw->due_at?->format('Y-m-d\\TH:i'),
            'due_label' => $hw->dueLabel(),
            'note' => $hw->note,
        ]);
    }

    /** Unlock or lock every class of one module at once. */
    public function toggleAll(Request $request, string $batch)
    {
        $batch = User::normalizeBatch($batch);
        abort_unless(Access::ownsBatch($request->user(), $batch), 403);
        $data = $request->validate(['module' => ['required', Rule::in(Tests::MODULES)], 'unlocked' => 'required|boolean']);
        foreach (array_keys(Tests::all()[$data['module']] ?? []) as $slug) {
            $where = ['batch' => $batch, 'module' => $data['module'], 'test_slug' => $slug];
            $data['unlocked'] ? ClassUnlock::firstOrCreate($where, ['teacher_id' => $request->user()->id]) : ClassUnlock::where($where)->delete();
        }

        return back()->with('ok', 'All '.ucfirst($data['module']).' classes '.($data['unlocked'] ? 'unlocked' : 'locked').' for '.$batch.'.');
    }
}
