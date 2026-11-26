<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnswerKey;
use App\Models\Result;
use App\Models\User;
use App\Support\Access;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $me = $request->user();
        // One course at a time: a teacher's own course, or the panel the admin opened (Reading / Writing).
        $course = $me->courseKey() ?? (session('panel') === 'writing' ? 'writing' : 'reading');
        // A teacher sees only the batch picked after login; the admin sees every batch.
        $batch = $me->isTeacher() ? Access::currentBatch($me) : null;
        $results = fn () => Access::scopeResults(Result::query(), $me)->where('module', $course)
            ->when($me->isTeacher(), fn ($q) => $q->whereHas('user', fn ($u) => $u->where('batch', $batch ?? '__none__')));

        $days = collect(range(13, 0))->map(fn ($d) => now()->subDays($d)->toDateString());
        $recent = $results()->where('created_at', '>=', now()->subDays(13)->startOfDay())->get(['module', 'created_at']);
        $perDay = $recent->groupBy(fn ($r) => $r->created_at->toDateString());
        $thisWeek = $recent->where('created_at', '>=', now()->subDays(6)->startOfDay())->count();
        $lastWeek = $recent->count() - $thisWeek;

        // Correct answers (score / total) as a % — no band scores on this site.
        $scored = $results()->where('total', '>', 0)->get(['user_id', 'score', 'total']);
        $pct = $scored->map(fn ($r) => $r->score / $r->total * 100);
        $bands = $pct; // kept for the view's counters
        $bandRows = collect(['0 – 39%' => [0, 39.99], '40 – 59%' => [40, 59.99], '60 – 74%' => [60, 74.99], '75 – 89%' => [75, 89.99], '90 – 100%' => [90, 100]])
            ->map(fn ($r, $label) => ['label' => $label, 'n' => $pct->filter(fn ($p) => $p >= $r[0] && $p <= $r[1])->count()])->values();

        return view('admin.dashboard', [
            'course' => $course,
            'students' => Access::scopeStudents(User::query(), $me)->when($me->isTeacher(), fn ($q) => $q->where('batch', $batch ?? '__none__'))->count(),
            'teachers' => User::where('role', 'teacher')->count(),
            'batches' => $me->isTeacher() ? ($batch ? 1 : 0) : count(Access::batchesFor($me)),
            'currentBatch' => $batch,
            'readingResults' => $results()->where('module', 'reading')->count(),
            'writingResults' => $results()->where('module', 'writing')->count(),
            'pendingWriting' => $results()->where('module', 'writing')->whereNull('feedback_sent_at')->count(),
            'avgBand' => $pct->isNotEmpty() ? round($pct->avg()) : null, // average % correct
            'avgScore' => $scored->isNotEmpty() ? round($scored->avg('score'), 1).' / '.round($scored->avg('total')) : null,
            'keys' => AnswerKey::count(),
            'chart' => $days->map(fn ($d) => [
                'day' => $d,
                'reading' => ($perDay[$d] ?? collect())->where('module', 'reading')->count(),
                'writing' => ($perDay[$d] ?? collect())->where('module', 'writing')->count(),
            ]),
            'thisWeek' => $thisWeek,
            'lastWeek' => $lastWeek,
            'today' => $results()->where('created_at', '>=', now()->startOfDay())->count(),
            'bandRows' => $bandRows,
            'bandCount' => $bands->count(),
            'latest' => $results()->with('user')->latest()->take(8)->get(),
            'homework' => $homework = ($course === 'reading' ? $this->homework($me) : collect()),
            'hwBatches' => $this->homeworkByBatch($homework),
            'batchStats' => $this->batchStats($me, $course),
            'topStudents' => $results()->where('total', '>', 0)->with('user:id,name,batch')->get()
                ->groupBy('user_id')->map(fn ($rs) => (object) [
                    'user' => $rs->first()->user,
                    'avg' => round($rs->sum('score') / $rs->sum('total') * 100), // % correct
                    'correct' => $rs->sum('score'), 'total' => $rs->sum('total'), 'n' => $rs->count(),
                ])
                ->filter(fn ($x) => $x->user)->sortByDesc('avg')->take(5)->values(),
        ]);
    }

    /** Students, hand-ins and average reading band for each batch this person sees. */
    private function batchStats(User $me, string $course): \Illuminate\Support\Collection
    {
        $batches = $me->isTeacher() ? array_filter([Access::currentBatch($me)]) : Access::batchesFor($me);
        $students = User::where('role', 'student')->whereIn('batch', $batches ?: ['__none__'])->get(['id', 'batch'])->groupBy('batch');
        $res = Result::where('module', $course)->whereIn('user_id', $students->flatten()->pluck('id'))->get(['user_id', 'module', 'band'])->groupBy(
            fn ($r) => optional($students->flatten()->firstWhere('id', $r->user_id))->batch
        );

        return collect($batches)->map(fn ($b) => (object) [
            'batch' => $b,
            'students' => count($students[$b] ?? []),
            'results' => count($res[$b] ?? []),
            'avg' => ($res[$b] ?? collect())->whereNotNull('band')->avg('band'),
        ])->sortByDesc('results')->values();
    }

    /**
     * Per batch: how many hand-ins were made / missed over all its homework,
     * the latest homework, and the students who missed BOTH of the last 2 homework.
     */
    private function homeworkByBatch(\Illuminate\Support\Collection $homework): \Illuminate\Support\Collection
    {
        return $homework->groupBy(fn ($h) => $h->hw->batch)->map(function ($items, $batch) {
            $recent = $items->sortByDesc(fn ($h) => $h->hw->created_at)->values();
            $missed2 = collect();
            if ($recent->count() >= 2) {
                $prevIds = collect($recent[1]->missing)->pluck('id');
                $missed2 = collect($recent[0]->missing)->filter(fn ($s) => $prevIds->contains($s->id))->values();
            }
            $done = $items->sum(fn ($h) => count($h->done));
            $total = $items->sum('total');

            return (object) [
                'batch' => $batch,
                'count' => $items->count(),
                'done' => $done,
                'missing' => $total - $done,
                'late' => $items->sum('late'),
                'rate' => $total ? round($done / $total * 100) : 0,
                'latest' => $recent->first(),
                'last2' => $recent->take(2),
                'missed2' => $missed2,
            ];
        })->sortBy('batch')->values();
    }

    /** Every homework set for the batches this person sees, with who has / has not handed it in. */
    private function homework(User $me): \Illuminate\Support\Collection
    {
        $batches = $me->isTeacher() ? array_filter([Access::currentBatch($me)]) : Access::batchesFor($me);
        $list = \App\Models\HomeworkAssignment::where('module', 'reading')->whereIn('batch', $batches ?: ['__none__'])
            ->orderByRaw('due_at IS NULL')->orderByDesc('due_at')->latest()->get();
        if ($list->isEmpty()) {
            return $list;
        }
        $students = User::where('role', 'student')->whereIn('batch', $list->pluck('batch')->unique())
            ->orderBy('name')->get(['id', 'name', 'batch'])->groupBy('batch');
        $handedIn = Result::where('kind', 'homework')->whereIn('user_id', $students->flatten()->pluck('id'))
            ->get(['id', 'user_id', 'module', 'test_slug', 'late', 'score', 'total', 'created_at'])
            ->keyBy(fn ($r) => $r->module.':'.$r->test_slug.':'.$r->user_id);

        return $list->map(function ($hw) use ($students, $handedIn) {
            $done = $missing = [];
            foreach ($students[$hw->batch] ?? [] as $s) {
                $r = $handedIn[$hw->module.':'.$hw->test_slug.':'.$s->id] ?? null;
                $r ? $done[] = ['student' => $s, 'result' => $r] : $missing[] = $s;
            }

            return (object) [
                'hw' => $hw,
                'title' => \App\Support\Tests::title($hw->module, $hw->test_slug),
                'done' => $done,
                'missing' => $missing,
                'late' => count(array_filter($done, fn ($d) => $d['result']->late)),
                'total' => count($done) + count($missing),
            ];
        });
    }
}
