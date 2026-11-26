<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MyResultsController;
use App\Http\Controllers\ResultController;
use App\Support\Access;
use App\Support\Timing;
use Illuminate\Support\Facades\Route;

/*
 | Roles
 |   admin   – everything
 |   teacher – own batches: students, class lock/unlock, reading results, writing checking
 |   student – classes unlocked for their batch, submit tests, own results
 */

Route::get('/login', [AuthController::class, 'show'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// Start page: guests log in first; admin / teacher then choose Reading or Writing.
Route::get('/', function () {
    $user = auth()->user();
    if (! $user) {
        return redirect()->route('login');
    }

    if ($user->isTeacher()) {
        return redirect()->route('admin.panel', $user->courseKey());
    }

    return $user->isAdmin() ? redirect()->route('admin.choose') : view('front_end.index');
})->name('home');

// ---------- Student site (every signed-in user) ----------
Route::middleware(['auth', 'role'])->group(function () {
    foreach (['speaking', 'listening', 'reading', 'writing'] as $module) {
        Route::get("/{$module}", function () use ($module) {
            // a teacher with several batches picks one before using the class pages
            if (Access::mustChooseBatch(auth()->user())) {
                return redirect()->route('batch.choose', ['back' => "/{$module}"]);
            }

            return view("front_end.pages.{$module}.index");
        })->name($module);
    }

    Route::view('/listening/test-1', 'front_end.pages.listening.test-1')->name('listening.test1');

    Route::get('/reading/class/{slug}', function (string $slug) {
        $class = collect(config('modules.reading.classes'))->firstWhere('slug', $slug);
        abort_unless($class && is_file($file = resource_path("reading/{$slug}.txt")), 404);
        if (! Access::canOpen(auth()->user(), 'reading', $slug)) {
            return redirect()->route('reading')->with('locked', 'This class is locked. Your teacher will unlock it.');
        }

        // apply the teacher-set times for the student's batch
        $times = Timing::forUser(auth()->user(), 'reading', $slug);
        $panes = \App\Support\ReadingSheet::load($file);
        foreach ($panes as $i => &$p) {
            $p['minutes'] = $times[(string) ($i + 1)] ?? null;
            $p['part'] = $i + 1;
        }
        unset($p);

        // Homework is never part of the class: it has its own page, opened once the teacher sets it.
        // Field names (p{tab}_q{n}) are fixed while parsing, so dropping tabs keeps answers and keys lined up.
        $panes = array_values(array_filter($panes, fn ($p) => ! \App\Support\Tests::isHomeworkTab($p['name'])));

        return view('front_end.pages.reading.class', [
            'class' => $class,
            'panes' => $panes,
            'totalMinutes' => $times['all'] ?? null,
            'homeworkCount' => \App\Support\Tests::homeworkCount('reading', $slug),
        ]);
    })->name('reading.class');

    // Homework page: only the "Homework …" tabs. Students need the homework set for their batch,
    // can hand it in once, and cannot start it after the deadline. Staff can always preview it.
    Route::get('/reading/class/{slug}/homework', function (string $slug) {
        $class = collect(config('modules.reading.classes'))->firstWhere('slug', $slug);
        abort_unless($class && is_file($file = resource_path("reading/{$slug}.txt")), 404);
        $user = auth()->user();
        $homework = Access::homeworkFor($user, 'reading', $slug);
        // Handed in already: the student can open it again to practise. The first attempt is what the teacher
        // sees; practice tries are marked on the page but never saved.
        $practice = ! $user->isStaff() && $homework && Access::homeworkDone($user, 'reading', $slug);
        if (! $user->isStaff() && ! $practice) {
            if (! $homework) {
                return redirect()->route('reading')->with('locked', 'This homework has not been set for your batch yet.');
            }
            if ($homework->overdue()) {
                return redirect()->route('reading')->with('locked', 'The deadline for this homework has passed. Please talk to your teacher.');
            }
        }

        $times = Timing::forUser($user, 'reading', $slug);
        $panes = \App\Support\ReadingSheet::load($file);
        foreach ($panes as $i => &$p) {
            $p['minutes'] = $practice ? null : ($times[(string) ($i + 1)] ?? null); // practice has no time limit
            $p['part'] = $i + 1;
        }
        unset($p);
        $panes = array_values(array_filter($panes, fn ($p) => \App\Support\Tests::isHomeworkTab($p['name'])));
        abort_unless($panes, 404);

        return view('front_end.pages.reading.class', [
            'class' => $class,
            'panes' => $panes,
            'totalMinutes' => null,
            'homework' => $homework,
            'isHomework' => true,
            'practice' => $practice,
            // exercises already handed in (homework goes in one exercise at a time)
            'doneParts' => $practice ? [] : Access::homeworkPartsDone($user, 'reading', $slug),
        ]);
    })->name('reading.homework');

    Route::get('/writing/test-1', function () {
        if (! Access::canOpen(auth()->user(), 'writing', 'test-1')) {
            return redirect()->route('writing')->with('locked', 'This class is locked. Your teacher will unlock it.');
        }

        return view('front_end.pages.writing.test-1', ['minutes' => Timing::forUser(auth()->user(), 'writing', 'test-1')['all']]);
    })->name('writing.test1');

    // Polled every 10 s by a test page: the batch's current time limits, and for reading the correct
    // answers once the teacher shows them — so a teacher's change reaches every student at once.
    Route::get('/tests/{module}/{slug}/live', function (string $module, string $slug) {
        abort_unless(\App\Support\Tests::exists($module, $slug), 404);
        $user = auth()->user();
        $out = ['times' => (object) Timing::forUser($user, $module, $slug), 'shown' => false];
        if ($module !== 'reading' || ! Access::answersShown($user, 'reading', $slug)) {
            return response()->json($out);
        }
        $key = \App\Models\AnswerKey::where('module', 'reading')->where('test_slug', $slug)->value('answers') ?? [];
        $last = \App\Models\Result::where('user_id', $user->id)->where('module', 'reading')->where('test_slug', $slug)->latest('id')->first();

        return response()->json($out + [
            'shown' => true,
            'answers' => (object) $key,
            'detail' => $last ? (object) \App\Support\Grader::grade($key, $last->answers)['detail'] : null,
            'score' => $last?->score,
            'total' => $last?->total,
        ]);
    })->name('tests.live');

    Route::post('/results/reading/{slug}/homework', [ResultController::class, 'homework'])->name('results.homework');
    Route::post('/results/{module}/{slug}', [ResultController::class, 'store'])->name('results.store');

    // Signature of the homework set for the student's batch: changes when a teacher sets / edits / removes one.
    Route::get('/my-homework/sig', fn () => response()->json([
        'sig' => Access::homeworkList(auth()->user())->map(fn ($h) => $h->id.'@'.$h->updated_at?->timestamp)->sort()->implode(','),
    ]))->name('my.homework.sig');

    Route::get('/my-results', [MyResultsController::class, 'index'])->name('my.results');
    Route::get('/my-results/{result}', [MyResultsController::class, 'show'])->name('my.results.show');
});

// ---------- Teacher / admin: which batch they are taking class with (lock / unlock works on it) ----------
Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::get('/choose-batch', function () {
        $batches = Access::batchesFor(auth()->user());
        abort_unless($batches, 403, 'No batch has been assigned to you yet. Please ask the admin.');

        return view('front_end.choose-batch', [
            'batches' => $batches,
            'current' => session('class_batch'),
            'counts' => \App\Models\User::where('role', 'student')->selectRaw('batch, COUNT(*) n')->groupBy('batch')->pluck('n', 'batch'),
        ]);
    })->name('batch.choose');

    Route::post('/choose-batch', function (\Illuminate\Http\Request $request) {
        $batch = \App\Models\User::normalizeBatch($request->input('batch'));
        abort_unless(in_array($batch, Access::batchesFor($request->user()), true), 403);
        session(['class_batch' => $batch]);
        $back = $request->input('back');

        return $back && str_starts_with($back, '/') && ! str_starts_with($back, '//') ? redirect($back) : redirect()->route($request->user()->courseKey() ?? 'reading');
    })->name('batch.choose.store');
});

// ---------- Staff panel: admin + teacher ----------
Route::middleware(['auth', 'role:admin,teacher'])->prefix('admin')->name('admin.')->group(function () {
    // Admin chooses Reading or Writing; a teacher goes straight to their own course.
    Route::get('/choose', fn () => auth()->user()->isTeacher()
        ? redirect()->route('admin.panel', auth()->user()->courseKey())
        : view('admin.choose'))->name('choose');
    Route::get('/panel/{panel}', function (string $panel) {
        abort_unless(in_array($panel, ['reading', 'writing'], true), 404);
        session(['panel' => $panel]);

        // Teacher: pick one of their assigned batches first, then the dashboard for that batch (same as the admin's).
        if (auth()->user()->isTeacher()) {
            session()->forget('class_batch');

            return redirect()->route('batch.choose', ['back' => '/admin']);
        }

        return redirect()->route($panel === 'writing' ? 'admin.writing.index' : 'admin.dashboard');
    })->name('panel');

    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    Route::get('/reading/results', [Admin\ReadingController::class, 'results'])->name('reading.results');
    Route::get('/reading/results/{result}', [Admin\ReadingController::class, 'show'])->name('reading.show');

    Route::get('/writing', [Admin\WritingController::class, 'index'])->name('writing.index');
    Route::get('/writing/{result}', [Admin\WritingController::class, 'show'])->name('writing.show');
    Route::post('/writing/{result}', [Admin\WritingController::class, 'mark'])->name('writing.mark');

    Route::get('/batches', [Admin\BatchController::class, 'index'])->name('batches.index');
    Route::get('/batches/{batch}', [Admin\BatchController::class, 'show'])->name('batches.show');
    Route::post('/batches/{batch}/toggle', [Admin\BatchController::class, 'toggle'])->name('batches.toggle');
    Route::post('/batches/{batch}/times', [Admin\BatchController::class, 'times'])->name('batches.times');
    Route::post('/batches/{batch}/answers', [Admin\BatchController::class, 'answers'])->name('batches.answers');
    Route::post('/batches/{batch}/homework', [Admin\BatchController::class, 'homework'])->name('batches.homework');
    Route::post('/batches/{batch}/toggle-all', [Admin\BatchController::class, 'toggleAll'])->name('batches.toggleAll');
});

// ---------- Admin only ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reading/answers', [Admin\ReadingController::class, 'answers'])->name('reading.answers');
    Route::post('/reading/answers', [Admin\ReadingController::class, 'save'])->name('reading.answers.save');
    Route::delete('/results/{result}', [Admin\ReadingController::class, 'destroy'])->name('results.destroy');

    Route::get('/promos', [Admin\PromoController::class, 'index'])->name('promos.index');
    Route::post('/promos', [Admin\PromoController::class, 'store'])->name('promos.store');
    Route::put('/promos/{promo}', [Admin\PromoController::class, 'update'])->name('promos.update');
    Route::post('/promos/{promo}/toggle', [Admin\PromoController::class, 'toggle'])->name('promos.toggle');
    Route::delete('/promos/{promo}', [Admin\PromoController::class, 'destroy'])->name('promos.destroy');

    Route::get('/users', [Admin\StudentController::class, 'index'])->name('students.index');
    Route::get('/users/logins.pdf', [Admin\StudentController::class, 'loginsPdf'])->name('students.logins.pdf');
    Route::get('/users/logins', [Admin\StudentController::class, 'logins'])->name('students.logins');
    Route::get('/users/create', [Admin\StudentController::class, 'create'])->name('students.create');
    Route::post('/users', [Admin\StudentController::class, 'store'])->name('students.store');
    Route::get('/users/{user}/edit', [Admin\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/users/{user}', [Admin\StudentController::class, 'update'])->name('students.update');
    Route::delete('/users/{user}', [Admin\StudentController::class, 'destroy'])->name('students.destroy');

    Route::get('/assign', [Admin\AssignController::class, 'index'])->name('assign.index');
    Route::post('/assign', [Admin\AssignController::class, 'store'])->name('assign.store');
    Route::delete('/assign/{assignment}', [Admin\AssignController::class, 'destroy'])->name('assign.destroy');
});
