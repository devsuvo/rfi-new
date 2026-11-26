{{-- Shared class list for a module index page. Pass $module (key in config/modules.php). --}}
@php
    $m = config("modules.$module");
    $viewer = auth()->user();
    // Class locks (like CD IELTS): every class starts locked; a teacher unlocks it for the batch they are teaching now.
    $lockable = in_array($module, \App\Support\Tests::MODULES, true);
    $classBatch = \App\Support\Access::currentBatch($viewer);            // teacher / admin
    $canControl = $lockable && $viewer?->isTeacher() && $classBatch;
    $unlockedNow = $canControl ? \App\Support\Access::unlockedFor($classBatch) : [];
    $shownNow = $canControl ? \App\Models\ClassUnlock::where('batch', $classBatch)->whereNotNull('answers_shown_at')->get()->map(fn ($u) => $u->module.':'.$u->test_slug)->all() : [];
    $keysNow = $canControl ? \App\Models\AnswerKey::where('module', $module)->pluck('test_slug')->all() : [];
    $hwNow = $canControl ? \App\Models\HomeworkAssignment::where('batch', $classBatch)->where('module', $module)->get()->keyBy('test_slug') : collect();
    $hwMine = \App\Support\Access::homeworkList($viewer);   // student: homework set for their batch
    $hwDone = $viewer?->isStudent() ? \App\Models\Result::where('user_id', $viewer->id)->where('kind', 'homework')->pluck('test_slug')->all() : [];
    // Split "Class 3 – Matching Names" into a number and a topic, and pick a kind for the card style.
    $cards = array_map(function ($c) use ($module, $canControl, $classBatch, $unlockedNow, $shownNow, $keysNow, $hwNow, $hwMine, $hwDone) {
        $slug = $c['slug'] ?? basename($c['url'] ?? '');
        if ($canControl && $slug) {
            // only homework has a time limit; class exercises are untimed
            $parts = collect(\App\Support\Timing::parts($module, $slug))->filter(fn ($p) => \App\Support\Tests::isHomeworkTab($p['name']));
            $over = \App\Support\Timing::overrides($classBatch, $module, $slug);
            $c['ctl'] = [
                'slug' => $slug,
                'on' => in_array("$module:$slug", $unlockedNow, true),
                'shown' => in_array("$module:$slug", $shownNow, true),
                'hasKey' => in_array($slug, $keysNow, true),
                'hwCount' => \App\Support\Tests::homeworkCount($module, $slug),
                'hw' => $hwNow->get($slug),
                'parts' => $parts->map(fn ($p, $k) => ['name' => $p['name'], 'count' => $p['count'], 'default' => $p['minutes'], 'minutes' => $over[$k] ?? $p['minutes']])->all(),
            ];
        }
        $num = preg_match('/^Class (\d+)\s*[–-]\s*(.+)$/u', $c['title'], $x) ? $x[1] : null;
        $topic = $num ? $x[2] : $c['title'];
        $kind = match (true) {
            $c['title'] === 'Orientation' => 'start',
            str_contains($topic, 'Progress Test') => 'test',
            str_contains($topic, 'Review') => 'review',
            default => 'lesson',
        };
        // Classes stay locked for a student until a teacher unlocks them for the student's batch.
        $locked = ! \App\Support\Access::canOpen(auth()->user(), $module, $c['slug'] ?? basename($c['url'] ?? ''));
        $c['myHw'] = $hwMine->get($module.':'.($c['slug'] ?? basename($c['url'] ?? '')));
        if ($locked) {
            $c['url'] = null;
        }
        $c['hwDone'] = in_array($c['slug'] ?? '', $hwDone, true);

        return $c + compact('num', 'topic', 'kind', 'locked');
    }, $m['classes']);
    $icons = ['start' => 'rocket_launch', 'test' => 'workspace_premium', 'review' => 'replay', 'lesson' => 'auto_stories'];
    $labels = ['start' => 'START HERE', 'test' => 'PROGRESS TEST', 'review' => 'REVIEW', 'lesson' => null];
    $lessons = count(array_filter($cards, fn ($c) => $c['kind'] === 'lesson'));
    $tests = count(array_filter($cards, fn ($c) => $c['kind'] === 'test'));
    $openForStudent = count(array_filter($cards, fn ($c) => ! $c['locked']));
@endphp
@extends('front_end.layout.app')

@section('title', $m['name'])

@push('styles')
<style>
    .mi-hero { position: relative; overflow: hidden; background: var(--hp-navy); color: #fff; isolation: isolate; }
    .mi-hero::before { content: ''; position: absolute; inset: 0; z-index: -1; background: radial-gradient(620px 340px at 92% -10%, {{ $m['tone'] }}99, transparent 70%), radial-gradient(420px 280px at 0% 130%, rgba(232,184,74,.25), transparent 70%); }
    .mi-hero::after { content: ''; position: absolute; inset: 0; z-index: -1; opacity: .5; background-image: linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px); background-size: 48px 48px; -webkit-mask-image: radial-gradient(ellipse at 85% 0%, #000, transparent 65%); mask-image: radial-gradient(ellipse at 85% 0%, #000, transparent 65%); }
    .mi-hero .wrap { padding: 40px 0 96px; display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 28px; }
    .mi { --tone: {{ $m['tone'] }}; --soft: {{ $m['soft'] }}; padding: 0 0 96px; margin-top: -56px; position: relative; }
    .mi-head { display: flex; align-items: center; gap: 20px; animation: rise .7s var(--ease) both; }
    .mi-head .ico { width: 68px; height: 68px; border-radius: 20px; background: linear-gradient(145deg, {{ $m['tone'] }}, {{ $m['tone'] }}cc); color: #fff; display: grid; place-items: center; box-shadow: 0 18px 36px -10px {{ $m['tone'] }}, inset 0 1px 0 rgba(255,255,255,.3); flex: none; }
    .mi-head .ico .material-icons-outlined { font-size: 34px; }
    .mi-head h1 { margin: 0; font-size: clamp(30px, 4.4vw, 50px); font-weight: 800; letter-spacing: -.035em; line-height: 1.05; }
    .mi-head p { margin: 6px 0 0; color: rgba(255,255,255,.6); font-size: 15px; }
    .back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: rgba(255,255,255,.7); margin-bottom: 28px; padding: 6px 14px 6px 8px; border-radius: 999px; border: 1px solid rgba(255,255,255,.14); transition: background .2s, color .2s; }
    .back:hover { color: #fff; background: rgba(255,255,255,.08); }
    .back .material-icons-outlined { font-size: 18px; }

    .mi-stats { display: flex; gap: 10px; animation: rise .7s .1s var(--ease) both; }
    .mi-stats div { min-width: 104px; padding: 14px 18px; border-radius: 18px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.12); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
    .mi-stats b { display: block; font-size: 28px; font-weight: 800; letter-spacing: -.03em; line-height: 1.1; }
    .mi-stats div:first-child b { color: var(--hp-gold-2); }
    .mi-stats span { font-size: 11px; font-weight: 700; letter-spacing: 1.6px; color: rgba(255,255,255,.55); text-transform: uppercase; }

    .cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 340px), 1fr)); gap: 18px; }
    .card { position: relative; overflow: hidden; isolation: isolate; display: flex; flex-direction: column; gap: 18px; min-height: 210px; padding: 22px; background: #fff; border: 1px solid var(--t-line); border-radius: 22px; box-shadow: var(--shadow-sm); transition: transform .4s var(--ease), box-shadow .4s var(--ease), border-color .4s; animation: rise .6s var(--ease) both; }
    @for ($i = 1; $i <= 16; $i++) .lock-wrap:nth-child({{ $i }}) .card { animation-delay: {{ 0.05 + $i * 0.04 }}s; } @endfor
    .card::before { content: ''; position: absolute; inset: 0; z-index: -1; background: radial-gradient(360px 180px at 100% 0%, var(--soft), transparent 70%); opacity: .7; transition: opacity .4s; }
    .card::after { content: attr(data-num); position: absolute; right: 18px; top: -6px; z-index: -1; font-family: var(--serif) !important; font-style: italic; font-size: 110px; line-height: 1; color: var(--tone); opacity: .09; transition: opacity .4s, transform .6s var(--ease); }
    a.card:hover { transform: translateY(-6px); box-shadow: var(--shadow-lg); border-color: transparent; }
    a.card:hover::before { opacity: 1; }
    a.card:hover::after { opacity: .18; transform: translateY(6px); }

    .card-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .card .ic { width: 48px; height: 48px; border-radius: 15px; background: var(--soft); color: var(--tone); display: grid; place-items: center; transition: background .3s, color .3s, box-shadow .3s; }
    .card .ic .material-icons-outlined { font-size: 24px; }
    a.card:hover .ic { background: var(--tone); color: #fff; box-shadow: 0 12px 24px -8px var(--tone); }
    .card .tag { font-size: 10px; font-weight: 800; letter-spacing: 1.6px; padding: 5px 10px; border-radius: 999px; background: var(--soft); color: var(--tone); }
    .card .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 2px; color: var(--t-muted); margin-bottom: 6px; }
    .card h3 { margin: 0; font-size: 18px; font-weight: 800; color: var(--hp-navy); letter-spacing: -.02em; line-height: 1.3; }
    .card-foot { margin-top: auto; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
    .chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .chips span { font-size: 12px; font-weight: 600; color: var(--t-muted); background: var(--t-bg); border: 1px solid var(--t-line); padding: 4px 10px; border-radius: 999px; }
    .card .go { width: 42px; height: 42px; border-radius: 50%; border: 1px solid var(--t-line); color: var(--hp-navy); display: grid; place-items: center; flex: none; transition: all .3s var(--ease); }
    .card .go .material-icons-outlined { font-size: 20px; }
    a.card:hover .go { background: var(--hp-navy); border-color: var(--hp-navy); color: #fff; transform: rotate(-45deg); }

    /* Orientation: the featured "start here" card spans the full row */
    .card.k-start { grid-column: 1 / -1; min-height: 0; flex-direction: row; align-items: center; gap: 22px; padding: 26px 28px; background: linear-gradient(110deg, var(--hp-navy), #16296B); border-color: transparent; color: #fff; }
    .card.k-start::before { background: radial-gradient(500px 220px at 100% 50%, {{ $m['tone'] }}66, transparent 70%); opacity: 1; }
    .card.k-start::after { color: var(--hp-gold); opacity: .14; content: 'Start'; font-size: 130px; top: -14px; }
    .card.k-start .ic { width: 60px; height: 60px; border-radius: 18px; background: linear-gradient(180deg, var(--hp-gold-2), var(--hp-gold)); color: var(--hp-navy); box-shadow: 0 12px 28px -8px rgba(232,184,74,.7); flex: none; }
    .card.k-start .ic .material-icons-outlined { font-size: 30px; }
    .card.k-start .eyebrow { color: var(--hp-gold-2); }
    .card.k-start h3 { color: #fff; font-size: 24px; }
    .card.k-start .chips span { background: rgba(255,255,255,.08); border-color: rgba(255,255,255,.14); color: rgba(255,255,255,.75); }
    .card.k-start .body { flex: 1; display: flex; flex-direction: column; gap: 12px; }
    .card.k-start .go { width: 52px; height: 52px; background: #fff; color: var(--hp-navy); border-color: #fff; }
    a.card.k-start:hover .go { background: var(--hp-gold); border-color: var(--hp-gold); color: var(--hp-navy); }

    /* Progress tests: gold accents */
    .card.k-test .ic, .card.k-test .tag { background: #FFF4D6; color: #A87A12; }
    a.card.k-test:hover .ic { background: linear-gradient(180deg, var(--hp-gold-2), var(--hp-gold)); color: var(--hp-navy); box-shadow: 0 12px 24px -8px rgba(232,184,74,.8); }
    .card.k-test::before { background: radial-gradient(360px 180px at 100% 0%, #FFF4D6, transparent 70%); }
    .card.k-test::after { color: var(--hp-gold); }

    .card.soon { opacity: .55; }
    .card .badge { font-size: 10px; font-weight: 800; letter-spacing: 1.6px; color: var(--t-muted); background: var(--t-bg); border: 1px solid var(--t-line); border-radius: 999px; padding: 5px 10px; white-space: nowrap; }

    @media (max-width: 640px) {
        .mi-stats { width: 100%; } .mi-stats div { flex: 1; min-width: 0; padding: 12px 14px; }
        .card.k-start { flex-wrap: wrap; } .card.k-start .go { display: none; }
    }
    /* Locked for a student */
    .card.is-locked { filter: grayscale(1); opacity: .6; cursor: not-allowed; }
    .card.is-locked:hover { transform: none; box-shadow: none; }
    .badge.locked { background: #E6E9F2 !important; color: #5E6A85 !important; }
    .lock-flash.ok-flash { background: #ECFDF3; color: #15803D; border: 1px solid #A7E3BF; }
    .lock-flash { position: relative; z-index: 2; display: flex; align-items: center; gap: 10px; background: #FEF3E2; color: #92400E; border-radius: 14px; padding: 14px 18px; margin-bottom: 18px; font-weight: 600; }

    /* Teacher bar / student bar */
    .teacher-bar { position: relative; z-index: 2; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin: 0 0 22px; padding: 14px 18px; border-radius: 16px; background: #fff; border: 1px solid var(--t-line); box-shadow: var(--shadow-sm); }
    .teacher-bar p { margin: 0; color: #4a5268; font-size: 14px; display: flex; align-items: center; gap: 10px; flex: 1; min-width: 240px; }
    .teacher-bar p > .material-icons-outlined { color: var(--tone); }
    .batch-chip { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 999px; background: var(--soft); color: #4a5268; font-size: 14px; }
    .batch-chip .material-icons-outlined { font-size: 18px; color: var(--tone); }
    .batch-chip b { color: var(--hp-navy); letter-spacing: .02em; }
    .batch-chip a { margin-left: 4px; color: var(--tone); font-weight: 700; text-decoration: underline; }

    /* Card wrapper + teacher controls */
    .lock-wrap { position: relative; display: flex; flex-direction: column; gap: 8px; }
    .lock-wrap > .card { flex: 1; }
    .lock-wrap.w-start { grid-column: 1 / -1; }
    /* Teacher control strip under each card */
    .t-ctl { display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; padding: 8px 10px; border: 1px solid var(--t-line); border-radius: 14px; background: #fff; box-shadow: 0 4px 14px -10px rgba(11,27,63,.25); }
    .unlock-btn { display: inline-flex; align-items: center; gap: 9px; padding: 5px 12px 5px 6px; border-radius: 999px; border: 1px solid #f1c7cc; background: #fff5f6; color: #be123c; font: 700 12.5px var(--sans); cursor: pointer; white-space: nowrap; transition: background .2s, border-color .2s, color .2s; }
    .unlock-btn .sw { position: relative; width: 34px; height: 20px; border-radius: 999px; background: #d6dae4; transition: background .2s; flex: none; }
    .unlock-btn .sw::after { content: ''; position: absolute; top: 2px; left: 2px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.25); transition: transform .2s; }
    .unlock-btn.is-on { background: #ecfdf3; border-color: #a7e3bf; color: #15803d; }
    .unlock-btn.is-on .sw { background: #22c55e; }
    .unlock-btn.is-on .sw::after { transform: translateX(14px); }
    .unlock-btn .u-on, .unlock-btn.is-on .u-off { display: none; }
    .unlock-btn.is-on .u-on { display: inline; }
    .unlock-btn:hover { border-color: currentColor; }
    .unlock-btn.is-busy { opacity: .6; pointer-events: none; }

    /* CD IELTS style: the lock pill floats in the middle of the card, faint until hovered */
    /* New-homework notification + countdown (students) */
    .hw-alert { display: flex; align-items: center; gap: 14px; margin: 0 0 14px; padding: 14px 16px; border-radius: 16px;
        background: linear-gradient(135deg, #FFF7E6, #FFEFD5); border: 1px solid #F8D9A0; box-shadow: 0 10px 24px -16px #D97706; animation: hwIn .5s cubic-bezier(.2,.9,.3,1.2); }
    @keyframes hwIn { from { opacity: 0; transform: translateY(-8px); } }
    .hw-alert-ic { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: #D97706; color: #fff; flex: none; animation: ring 1.6s ease-in-out 3; }
    @keyframes ring { 0%,100% { transform: rotate(0); } 10%,30% { transform: rotate(-14deg); } 20%,40% { transform: rotate(14deg); } 50% { transform: rotate(0); } }
    .hw-alert-txt { flex: 1; min-width: 0; display: grid; gap: 2px; font-size: 13.5px; color: #78350F; }
    .hw-alert-txt > b { font-size: 15px; color: #451A03; }
    .hw-alert-go { padding: 9px 16px; border-radius: 999px; background: #D97706; color: #fff; font-weight: 700; font-size: 13px; text-decoration: none; white-space: nowrap; }
    .hw-alert-x { border: 0; background: none; font-size: 22px; line-height: 1; color: #92400E; cursor: pointer; padding: 4px; }
    .hw-cd { font-variant-numeric: tabular-nums; }
    .hw-cd.soon { color: #DC2626; }
    .hw-cd-chip { display: inline-flex; align-items: center; padding: 3px 10px; border-radius: 999px; background: #FFF7E6; color: #B45309; font-size: 12px; font-weight: 700; }
    .hw-cd-chip.soon { background: #FEF2F2; color: #DC2626; }
    @media (max-width: 640px) { .hw-alert { flex-wrap: wrap; } .hw-alert-go { order: 3; width: 100%; text-align: center; } }

    .hw-step { margin: 10px 0 2px; font-size: 12px; font-weight: 700; color: var(--t-muted); text-align: left; }
    .hw-quick button.on { background: #D97706; border-color: #D97706; color: #fff; }
    .hw-when { margin: 10px 0 0 !important; padding: 8px 10px; border-radius: 10px; background: #FFF7E6; color: #92400E; font-size: 13px; font-weight: 600; }
    .card-box { position: relative; display: flex; flex: 1; }
    .card-box > .card { flex: 1; }
    .lock-wrap > .card-box ~ .t-ctl { padding: 0 2px; border: 0; background: none; box-shadow: none; }
    .card-box .unlock-btn { position: absolute; z-index: 3; top: 50px; left: 40%; transform: translate(-50%, -50%);
        gap: 6px; padding: 7px 14px 7px 10px; border: 1.5px solid #d6dae4; background: rgba(255,255,255,.88); color: #4a5268;
        font-weight: 600; opacity: .5; backdrop-filter: blur(4px); box-shadow: 0 6px 16px -10px rgba(11,27,63,.4);
        transition: opacity .25s, transform .2s, background .2s, border-color .2s, color .2s, box-shadow .2s; }
    .card-box .unlock-btn .material-icons-outlined { font-size: 17px; }
    .card-box:hover .unlock-btn, .card-box .unlock-btn:focus-visible { opacity: 1; }
    .card-box .unlock-btn > span { display: none; align-items: center; gap: 6px; }
    .card-box .unlock-btn:not(.is-on) .u-locked, .card-box .unlock-btn.is-on .u-unlocked { display: inline-flex; }
    .card-box .unlock-btn:not(.is-on):hover .u-locked, .card-box .unlock-btn.is-on:hover .u-unlocked { display: none; }
    .card-box .unlock-btn:not(.is-on):hover .u-unlock, .card-box .unlock-btn.is-on:hover .u-relock { display: inline-flex; }
    .card-box .unlock-btn:hover { transform: translate(-50%, calc(-50% - 1px)); background: #fff; border-color: #0891B2; color: #0891B2; box-shadow: 0 10px 20px -10px #0891B2; }
    .card-box .unlock-btn.is-on { background: rgba(231,248,238,.92); border-color: #22c55e; color: #15803d; }
    .card-box .unlock-btn.is-on:hover { background: #fff1f2; border-color: #e2263c; color: #e2263c; box-shadow: 0 10px 20px -10px #e2263c; }
    .lock-wrap:has(.unlock-btn:not(.is-on)) .card { background: #f6f7fa; }
    .lock-wrap:has(.unlock-btn:not(.is-on)) > .card { background: #f6f7fa; }
    .bulk { display: inline-flex; gap: 6px; align-items: center; flex-wrap: wrap; }
    .bulk .cnt { font-size: 13px; color: #4a5268; margin-right: 4px; }
    .bulk .cnt b { color: #15803d; }
    .bulk button { padding: 7px 12px; border-radius: 999px; border: 1px solid var(--t-line); background: #fff; font: 700 12.5px var(--sans); color: #4a5268; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; }
    .bulk button .material-icons-outlined { font-size: 16px; }
    .bulk button:hover { border-color: var(--tone); color: var(--tone); }
    .tb-all { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; margin-bottom: 4px; border-radius: 12px; background: var(--soft); font-size: 13px; color: #4a5268; text-align: left; }
    .tb-all .stepper input { width: 54px; }
    .tb-all button.apply { padding: 7px 12px; border-radius: 10px; border: 0; background: var(--tone); color: #fff; font: 700 12.5px var(--sans); cursor: pointer; }
    .tb-total { display: flex; justify-content: space-between; padding: 10px 0 0; border-top: 2px solid var(--t-line); font-size: 14px; color: var(--hp-navy); }
    .t-tools { display: flex; flex-wrap: wrap; gap: 6px; }
    .tool { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; border: 1px solid var(--t-line); background: #fff; color: #4a5268; font: 600 12.5px var(--sans); cursor: pointer; transition: border-color .2s, color .2s, background .2s; }
    .tool .material-icons-outlined { font-size: 16px; }
    button.tool:hover { border-color: var(--tone); color: var(--tone); }
    .tool.is-set { background: var(--soft); border-color: var(--tone); color: var(--tone); }
    .tool.muted { cursor: default; color: #9aa2b5; }

    /* Homework */
    .hw-tool.is-set { background: #FFF7E6; border-color: #F59E0B; color: #B45309; }
    .hw-tool.is-late { background: #FEF2F2; border-color: #F87171; color: #B91C1C; }
    .hw-badge { display: inline-flex; align-items: center; gap: 5px; padding: 5px 10px; border-radius: 999px; background: #FFF7E6; color: #B45309; font: 700 12px var(--sans); border: 1px solid #FCD34D; }
    .hw-badge.late { background: #FEF2F2; color: #B91C1C; border-color: #FCA5A5; }
    .hw-badge .material-icons-outlined { font-size: 15px; }
    .hw-badge.done { background: #E8F7EE; color: #15803D; border-color: #B7E4C7; }
    :root[data-theme="dark"] .hw-badge.done { background: #12301F; color: #4ADE80; border-color: #1E5A36; }
    /* "Your homework" panel — site navy / teal, one status colour per row (to do · submitted · deadline passed) */
    .hw-panel { --hw-bg: #fff; --hw-line: #E4E8F0; --hw-row: #F8FAFC; --hw-ink: var(--hp-navy); --hw-muted: #64708A;
        --hw-teal: #0AA3B5; --hw-teal-soft: #E3F6F8; --hw-green: #15803D; --hw-green-soft: #E8F7EE; --hw-red: #C2410C; --hw-red-soft: #FDEEE8;
        margin: 0 0 22px; padding: 20px; border-radius: 18px; background: var(--hw-bg); border: 1px solid var(--hw-line); box-shadow: 0 1px 2px rgba(11,27,63,.04), 0 12px 30px -22px rgba(11,27,63,.25); }
    .hw-head { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .hw-head-ic { width: 40px; height: 40px; flex: none; display: grid; place-items: center; border-radius: 12px; background: var(--hw-teal-soft); color: var(--hw-teal); }
    .hw-head-ic .material-icons-outlined { font-size: 22px; }
    .hw-panel h2 { margin: 0; font-size: 17px; color: var(--hw-ink); }
    .hw-sum { margin: 2px 0 0; font-size: 13px; color: var(--hw-muted); }
    .hw-sum b { color: var(--hw-ink); }
    .hw-list { display: grid; gap: 8px; }
    .hw-item { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; padding: 12px 14px; border-radius: 14px; background: var(--hw-row); border: 1px solid transparent; text-decoration: none; color: inherit; transition: border-color .2s, background .2s, box-shadow .2s; }
    a.hw-item:hover { background: var(--hw-bg); border-color: var(--hw-teal); box-shadow: 0 8px 20px -14px rgba(10,163,181,.55); }
    .hw-st { width: 34px; height: 34px; flex: none; display: grid; place-items: center; border-radius: 50%; }
    .hw-st .material-icons-outlined { font-size: 19px; }
    .hw-item.open .hw-st { background: var(--hw-teal-soft); color: var(--hw-teal); }
    .hw-item.done .hw-st { background: var(--hw-green-soft); color: var(--hw-green); }
    .hw-item.late .hw-st { background: var(--hw-red-soft); color: var(--hw-red); }
    .hw-main { flex: 1; min-width: 200px; display: grid; gap: 3px; }
    .hw-item b { color: var(--hw-ink); font-size: 14.5px; }
    .hw-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-size: 12.5px; color: var(--hw-muted); }
    .hw-meta .ok { color: var(--hw-green); font-weight: 700; }
    .hw-meta .bad { color: var(--hw-red); font-weight: 700; }
    .hw-item small { color: var(--hw-muted); font-size: 12.5px; width: 100%; padding-left: 48px; }
    .hw-act { display: inline-flex; align-items: center; gap: 5px; padding: 8px 14px; border-radius: 10px; font: 700 13px var(--sans); white-space: nowrap; transition: background .2s, color .2s, border-color .2s; }
    .hw-act .material-icons-outlined { font-size: 17px; }
    .hw-act.primary { background: var(--hw-teal); color: #fff; }
    a.hw-item:hover .hw-act.primary { background: #08899A; }
    .hw-act.ghost { background: transparent; color: var(--hw-ink); border: 1px solid var(--hw-line); }
    a.hw-item:hover .hw-act.ghost { border-color: var(--hw-teal); color: var(--hw-teal); background: var(--hw-teal-soft); }
    .hw-act.muted { color: var(--hw-muted); font-weight: 600; padding-right: 0; }
    .hw-panel .hw-cd-chip { background: var(--hw-teal-soft); color: var(--hw-teal); border: 0; padding: 2px 9px; }
    .hw-panel .hw-cd-chip.soon { background: var(--hw-red-soft); color: var(--hw-red); }
    :root[data-theme="dark"] .hw-panel { --hw-bg: #151C31; --hw-line: #26304A; --hw-row: #111829; --hw-ink: #E7ECF7; --hw-muted: #94A0BC;
        --hw-teal: #1BB8CB; --hw-teal-soft: #11313A; --hw-green: #4ADE80; --hw-green-soft: #12301F; --hw-red: #F2885B; --hw-red-soft: #3A2016; box-shadow: none; }
    :root[data-theme="dark"] .hw-panel .hw-item, :root[data-theme="dark"] .hw-panel div.hw-item:hover { background: var(--hw-row); border-color: transparent; }
    :root[data-theme="dark"] .hw-panel a.hw-item:hover { background: #18213A; border-color: var(--hw-teal); }
    :root[data-theme="dark"] .hw-panel .hw-cd-chip { background: var(--hw-teal-soft); color: var(--hw-teal); border: 0; }
    :root[data-theme="dark"] .hw-panel .hw-cd-chip.soon { background: var(--hw-red-soft); color: var(--hw-red); }
    @media (max-width: 560px) {
        .hw-panel { padding: 16px; }
        .hw-act { width: 100%; justify-content: center; }
        .hw-item small { padding-left: 0; }
    }
    .hw-form { text-align: left; display: grid; gap: 12px; margin-bottom: 16px; }
    .hw-form label { display: grid; gap: 6px; font-size: 13px; font-weight: 600; color: #4a5268; }
    .hw-form input, .hw-form textarea { border: 1px solid var(--t-line); border-radius: 10px; padding: 10px 12px; font: 500 14px var(--sans); color: var(--hp-navy); resize: vertical; }
    .hw-quick { display: flex; gap: 6px; flex-wrap: wrap; }
    .hw-quick button { padding: 5px 10px; border-radius: 999px; border: 1px solid var(--t-line); background: #fff; font: 600 12px var(--sans); color: #4a5268; cursor: pointer; }
    .hw-quick button:hover { border-color: #F59E0B; color: #B45309; }
    .mi-acts .b-remove { background: #fff1f2; color: #e2263c; }

    /* Dialogs + toast */
    .mi-modal { position: fixed; inset: 0; z-index: 120; display: grid; place-items: center; padding: 16px; background: rgba(11,26,58,.45); backdrop-filter: blur(4px); opacity: 0; pointer-events: none; transition: opacity .2s; }
    .mi-modal.show { opacity: 1; pointer-events: auto; }
    .mi-box { width: 100%; max-width: 460px; max-height: calc(100vh - 32px); overflow: auto; padding: 26px 22px 20px; border-radius: 22px; background: #fff; text-align: center; box-shadow: 0 30px 70px -20px rgba(11,26,58,.5); }
    .mi-box-icon { width: 54px; height: 54px; margin: 0 auto 12px; border-radius: 16px; display: grid; place-items: center; background: var(--soft); color: var(--tone); }
    .mi-box h3 { margin: 0 0 6px; font-size: 19px; color: var(--hp-navy); }
    .mi-box p { margin: 0 0 16px; font-size: 14px; color: #5b6478; }
    .mi-acts { display: grid; gap: 8px; }
    .mi-acts button { padding: 12px; border-radius: 12px; border: 0; font: 700 14px var(--sans); cursor: pointer; background: #f1f3f8; color: #4a5268; }
    .mi-acts .b-yes { background: var(--tone); color: #fff; }
    .tb-rows { text-align: left; margin-bottom: 8px; }
    .tb-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 9px 0; border-top: 1px solid var(--t-line); }
    .tb-part b { display: block; color: var(--hp-navy); font-size: 14px; }
    .tb-part small { color: var(--t-muted); font-size: 12px; }
    .stepper { display: inline-flex; align-items: center; gap: 4px; }
    .stepper button { width: 34px; height: 34px; border-radius: 10px; border: 1px solid var(--t-line); background: #fff; font-size: 18px; cursor: pointer; color: var(--hp-navy); }
    .stepper input { width: 60px; text-align: center; border: 1px solid var(--t-line); border-radius: 10px; padding: 7px 4px; font: 700 15px var(--sans); color: var(--hp-navy); }
    .stepper input.changed { border-color: var(--tone); color: var(--tone); }
    .stepper em { font-style: normal; font-size: 12px; color: var(--t-muted); }
    .tb-hint { font-size: 12px !important; margin: 6px 0 14px !important; }
    .mi-toast { position: fixed; left: 50%; bottom: 26px; z-index: 130; transform: translate(-50%, 20px); background: var(--hp-navy); color: #fff; padding: 12px 20px; border-radius: 14px; font-weight: 600; font-size: 14px; opacity: 0; pointer-events: none; transition: opacity .25s, transform .25s; }
    .mi-toast.show { opacity: 1; transform: translate(-50%, 0); }

    /* ===== CD IELTS look: light header card with illustration, white class cards ===== */
    .mi-hero { background: transparent; color: var(--t-ink); }
    .mi-hero::before, .mi-hero::after { display: none; }
    .mi-hero .wrap { margin-top: 32px; padding: 36px 40px; min-height: 252px; background: linear-gradient(100deg, #fff 45%, {{ $m['soft'] }}); border: 1px solid var(--t-line); border-radius: 24px; position: relative; overflow: hidden; align-items: center; box-shadow: 0 24px 50px -36px rgba(11,27,63,.35); }
    .mi-hero .wrap::after { content: ''; position: absolute; right: -60px; top: 50px; width: 420px; height: 420px; border-radius: 50%; border: 1px solid {{ $m['tone'] }}33; pointer-events: none; }
    .mi { margin-top: 0; padding-top: 28px; }
    .mi-head .ico { width: 60px; height: 60px; border-radius: 16px; background: {{ $m['tone'] }}; box-shadow: 0 14px 28px -12px {{ $m['tone'] }}; }
    .mi-head .ico .material-icons-outlined { font-size: 30px; }
    .mi-head h1 { color: #0B1B3F; font-size: clamp(28px, 3.4vw, 40px); position: relative; padding-bottom: 12px; }
    .mi-head h1::after { content: ''; position: absolute; left: 0; bottom: 0; width: 48px; height: 3px; border-radius: 3px; background: {{ $m['tone'] }}; }
    .mi-head p { color: var(--t-muted); margin-top: 10px; }
    .back { color: var(--t-muted); border-color: var(--t-line); background: #fff; margin-bottom: 20px; }
    .back:hover { color: #0B1B3F; background: #fff; border-color: {{ $m['tone'] }}; }
    .mi-art { position: relative; z-index: 1; height: 190px; width: auto; margin-left: auto; animation: rise .7s .1s var(--ease) both; }
    .mi-stats { position: absolute; left: 40px; bottom: 22px; z-index: 1; }
    .mi-hero .wrap:has(.mi-stats) { padding-bottom: 96px; }
    .mi-stats div { min-width: 0; padding: 8px 14px; border-radius: 12px; background: #fff; border-color: var(--t-line); display: flex; align-items: baseline; gap: 6px; }
    .mi-stats b { font-size: 18px; color: #0B1B3F; }
    .mi-stats div:first-child b { color: {{ $m['tone'] }}; }
    .mi-stats span { color: var(--t-muted); font-size: 10.5px; }

    .cards { grid-template-columns: repeat(auto-fill, minmax(min(100%, 330px), 1fr)); gap: 22px; }
    .card { border-radius: 18px; min-height: 195px; box-shadow: 0 1px 2px rgba(11,27,63,.04); border-color: var(--t-line); }
    .card::before { inset: auto; right: -46px; top: -46px; width: 130px; height: 130px; border-radius: 50%; background: var(--soft); opacity: 1; transition: transform .35s ease; }
    .card::after { font-family: inherit !important; font-style: normal; font-size: 13px; font-weight: 700; color: #0B1B3F; opacity: 1; right: 26px; top: 40px; z-index: 0; letter-spacing: .5px; }
    a.card:hover { transform: translateY(-4px); border-color: var(--tone); box-shadow: 0 22px 40px -22px rgba(11,27,63,.35); }
    a.card:hover::before { transform: scale(1.25); }
    a.card:hover::after { opacity: 1; transform: none; }
    .card .ic { width: 56px; height: 56px; border-radius: 14px; }
    .card .card-top .tag { margin-right: 40px; }
    .card h3 { font-size: 20px; color: #0B1B3F; }
    .card .go { background: var(--soft); border-color: transparent; color: var(--tone); }
    a.card:hover .go { background: var(--tone); border-color: var(--tone); color: #fff; transform: translateX(3px); }

    .card.k-start { background: #fff; color: var(--t-ink); border-color: var(--t-line); }
    .card.k-start::before { background: var(--soft); }
    .card.k-start::after { content: ''; }
    .card.k-start .ic { background: {{ $m['tone'] }}; color: #fff; box-shadow: 0 12px 24px -10px {{ $m['tone'] }}; }
    .card.k-start .eyebrow { color: {{ $m['tone'] }}; }
    .card.k-start h3 { color: #0B1B3F; }
    .card.k-start .chips span { background: var(--t-bg); border-color: var(--t-line); color: var(--t-muted); }
    .card.k-start .go { background: {{ $m['tone'] }}; border-color: {{ $m['tone'] }}; color: #fff; }
    a.card.k-start:hover .go { background: #0B1B3F; border-color: #0B1B3F; color: #fff; }

    .card.is-locked { filter: none; opacity: 1; background: #F3F5F9; }
    .card.is-locked::before { background: #E9EEF5; }
    .card.is-locked .ic { background: #E4E8EF; color: #8792A6; }
    .card.is-locked h3, .card.is-locked .eyebrow, .card.is-locked::after { color: #8792A6; }
    @media (max-width: 760px) { .mi-hero .wrap { padding: 26px 22px; } .mi-art { display: none; } .mi-stats { position: static; } .mi-hero .wrap:has(.mi-stats) { padding-bottom: 26px; } }

    /* ===== Module hero ===== */
    .mi-hero .wrap.mh { display: grid; grid-template-columns: minmax(0, 1fr) auto; align-items: center; gap: 28px; margin-top: 28px; padding: 30px 44px 34px; min-height: 0; border-radius: 28px;
        background: radial-gradient(520px 280px at 86% 45%, {{ $m['tone'] }}26, transparent 70%), radial-gradient(420px 220px at 0% 110%, rgba(232,184,74,.14), transparent 70%), linear-gradient(120deg, #fff 38%, {{ $m['soft'] }});
        box-shadow: 0 1px 2px rgba(11,27,63,.04), 0 30px 60px -42px rgba(11,27,63,.45); animation: rise .6s var(--ease) both; }
    .mi-hero .wrap.mh::before { content: ''; position: absolute; inset: 0; pointer-events: none; background-image: radial-gradient(rgba(11,27,63,.09) 1px, transparent 1.2px); background-size: 18px 18px;
        -webkit-mask-image: linear-gradient(100deg, transparent 40%, #000 85%); mask-image: linear-gradient(100deg, transparent 40%, #000 85%); }
    .mi-hero .wrap.mh::after { display: none; }
    .mh-copy { position: relative; z-index: 1; min-width: 0; }
    .mh .back { margin-bottom: 18px; }
    .mh-eyebrow { display: flex; width: fit-content; align-items: center; gap: 8px; margin-bottom: 14px; padding: 5px 12px 5px 10px; border-radius: 999px; background: {{ $m['tone'] }}1A; color: {{ $m['tone'] }}; font-size: 10.5px; font-weight: 800; letter-spacing: 1.8px; text-transform: uppercase; }
    .mh-eyebrow i { width: 7px; height: 7px; border-radius: 50%; background: {{ $m['tone'] }}; animation: mhPulse 2.2s ease-out infinite; }
    @keyframes mhPulse { 0% { box-shadow: 0 0 0 0 {{ $m['tone'] }}99; } 70%, 100% { box-shadow: 0 0 0 8px {{ $m['tone'] }}00; } }
    .mh .mi-head .ico { width: 64px; height: 64px; border-radius: 18px; background: linear-gradient(145deg, {{ $m['tone'] }}, {{ $m['tone'] }}CC); box-shadow: 0 16px 30px -12px {{ $m['tone'] }}, inset 0 1px 0 rgba(255,255,255,.35); transition: transform .4s var(--ease); }
    .mh .mi-head:hover .ico { transform: rotate(-6deg) scale(1.05); }
    .mh .mi-head h1 { font-size: clamp(30px, 3.8vw, 44px); }
    .mh .mi-head h1 .hl { color: {{ $m['tone'] }}; }
    .mh-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 26px; animation: rise .7s .15s var(--ease) both; }
    .mh-stat { display: flex; align-items: center; gap: 12px; padding: 9px 18px 9px 9px; border-radius: 16px; background: rgba(255,255,255,.78); border: 1px solid var(--t-line); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); box-shadow: 0 8px 20px -16px rgba(11,27,63,.35); }
    .mh-stat .si { width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; background: {{ $m['tone'] }}1A; color: {{ $m['tone'] }}; flex: none; }
    .mh-stat .si .material-icons-outlined { font-size: 21px; }
    .mh-stat b { display: block; font-size: 20px; font-weight: 800; line-height: 1.15; color: #0B1B3F; letter-spacing: -.02em; }
    .mh-stat b em { font-style: normal; font-size: 14px; color: var(--t-muted); font-weight: 700; }
    .mh-stat small { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: var(--t-muted); }
    .mh-bar { display: block; width: 110px; height: 5px; margin-top: 6px; border-radius: 5px; background: var(--t-line); overflow: hidden; }
    .mh-bar i { display: block; height: 100%; border-radius: inherit; background: {{ $m['tone'] }}; transform-origin: left; animation: mhGrow 1.1s .3s var(--ease) both; }
    @keyframes mhGrow { from { transform: scaleX(0); } }

    .mh-art { position: relative; z-index: 1; width: 260px; height: 220px; display: grid; place-items: center; margin-right: 8px; }
    .mh-art > * { grid-area: 1 / 1; }
    .mh-art .ring { border-radius: 50%; border: 1.5px dashed {{ $m['tone'] }}55; animation: mhSpin 40s linear infinite; }
    .mh-art .r1 { width: 230px; height: 230px; }
    .mh-art .r2 { width: 290px; height: 290px; border-style: solid; border-color: {{ $m['tone'] }}22; animation-direction: reverse; animation-duration: 60s; }
    @keyframes mhSpin { to { transform: rotate(360deg); } }
    .mh-art .blob { width: 184px; height: 184px; border-radius: 50%; background: radial-gradient(circle at 35% 30%, #fff, {{ $m['soft'] }} 75%); box-shadow: 0 30px 50px -24px {{ $m['tone'] }}AA, inset 0 -8px 20px {{ $m['tone'] }}1F; }
    .mh-art img { position: relative; height: 172px; width: auto; animation: mhFloat 5s ease-in-out infinite; filter: drop-shadow(0 14px 14px rgba(11,27,63,.12)); }
    @keyframes mhFloat { 50% { transform: translateY(-9px) rotate(-1.5deg); } }
    .mh-art .spark { width: 9px; height: 9px; border-radius: 50%; background: {{ $m['tone'] }}; opacity: .7; animation: mhTwinkle 3s ease-in-out infinite; }
    .mh-art .s1 { transform: translate(-112px, -78px); }
    .mh-art .s2 { transform: translate(118px, -60px); background: var(--hp-red); width: 7px; height: 7px; animation-delay: .8s; }
    .mh-art .s3 { transform: translate(104px, 86px); background: var(--hp-gold); animation-delay: 1.6s; }
    @keyframes mhTwinkle { 50% { opacity: .15; scale: .6; } }

    :root[data-theme="dark"] .mi-hero .wrap.mh { background: radial-gradient(520px 280px at 86% 45%, {{ $m['tone'] }}33, transparent 70%), radial-gradient(420px 220px at 0% 110%, rgba(232,184,74,.08), transparent 70%), linear-gradient(120deg, #151C31 38%, #10283A); border-color: #26304A; box-shadow: 0 30px 60px -40px rgba(0,0,0,.9); }
    :root[data-theme="dark"] .mi-hero .wrap.mh::before { background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1.2px); }
    :root[data-theme="dark"] .mh-eyebrow { background: {{ $m['tone'] }}26; }
    :root[data-theme="dark"] .mh-stat { background: rgba(21,28,49,.72); border-color: #26304A; box-shadow: none; }
    :root[data-theme="dark"] .mh-stat b { color: #E7ECF7; }
    :root[data-theme="dark"] .mh-stat .si { background: {{ $m['tone'] }}26; }
    :root[data-theme="dark"] .mh-art .blob { background: radial-gradient(circle at 35% 30%, #F2F8FA, #BBD9DF 75%); box-shadow: 0 30px 60px -20px {{ $m['tone'] }}66; }

    @media (max-width: 860px) {
        .mi-hero .wrap.mh { grid-template-columns: 1fr; padding: 24px 22px 26px; }
        .mh-art { display: none; }
    }
</style>
@endpush

@section('content')
<section class="mi-hero"><div class="wrap mh">
    <div class="mh-copy">
        <a href="{{ route('home') }}" class="back"><span class="material-icons-outlined">arrow_back</span>All modules</a>
        <div class="mh-eyebrow"><i></i>IELTS Academic · {{ $m['name'] }}</div>
        <div class="mi-head">
            <span class="ico"><span class="material-icons-outlined">{{ $m['icon'] }}</span></span>
            <div>
                <h1>{!! str_replace('IELTS', '<span class="hl">IELTS</span>', e($m['heading'] ?? $m['name'])) !!}</h1>
                <p>Choose a class to start practising.</p>
            </div>
        </div>
        @if (count($cards) > 1)
            <div class="mh-meta">
                <div class="mh-stat"><span class="si"><span class="material-icons-outlined">view_module</span></span><div><b>{{ count($cards) }}</b><small>Sessions</small></div></div>
                @if ($lockable && $viewer?->isStudent() && $viewer->batch)
                    <div class="mh-stat"><span class="si"><span class="material-icons-outlined">lock_open</span></span><div><b>{{ $openForStudent }}<em>/{{ count($cards) }}</em></b><small>Open for you</small>
                        <span class="mh-bar"><i style="width: {{ round($openForStudent / max(count($cards), 1) * 100) }}%"></i></span></div></div>
                @endif
            </div>
        @endif
    </div>
    @if (file_exists(public_path('images/illustrations/'.$module.'.svg')))
        <div class="mh-art" aria-hidden="true">
            <span class="ring r1"></span><span class="ring r2"></span><span class="blob"></span>
            <span class="spark s1"></span><span class="spark s2"></span><span class="spark s3"></span>
            <img src="{{ asset('images/illustrations/'.$module.'.svg') }}" alt="">
        </div>
    @endif
</div></section>

<section class="mi wrap">
    @if (session('submitted'))
        <div class="lock-flash ok-flash" role="status"><span class="material-icons-outlined">check_circle</span>{{ session('submitted') }}</div>
    @endif
    @if (session('locked'))
        <div class="lock-flash"><span class="material-icons-outlined">lock</span>{{ session('locked') }}</div>
    @endif

    @if ($lockable && $viewer?->isTeacher())
        <div class="teacher-bar">
            @if ($classBatch)
                <p><span class="material-icons-outlined">lock_open</span><span>Use the switch under a class to open it for this batch. <b>⏱</b> sets the homework time.</span></p>
                @if ($canControl)
                    <span class="bulk"><span class="cnt"><b id="openCount">0</b> / <span id="allCount">0</span> open</span>
                        <button type="button" data-bulk="1"><span class="material-icons-outlined">lock_open</span>Unlock all</button>
                        <button type="button" data-bulk="0"><span class="material-icons-outlined">lock</span>Lock all</button>
                    </span>
                @endif
                <span class="batch-chip"><span class="material-icons-outlined">groups</span>Batch <b>{{ strtoupper($classBatch) }}</b>
                    <a href="{{ route('batch.choose', ['back' => request()->getRequestUri()]) }}" title="Switch to another of your batches">Change</a>
                </span>
            @else
                <p><span class="material-icons-outlined">info</span>You have no batch assigned yet, so you cannot unlock classes. Please ask the admin.</p>
            @endif
        </div>
    @elseif ($lockable && $viewer?->isStudent() && $viewer->batch)
        <div class="teacher-bar">
            <p><span class="material-icons-outlined">{{ $openForStudent ? 'lock_open' : 'lock' }}</span><span><b>{{ $openForStudent }}</b> of {{ count($cards) }} {{ count($cards) === 1 ? 'class is' : 'classes are' }} open for your batch. Your teacher opens the rest.</span></p>
            <span class="batch-chip"><span class="material-icons-outlined">groups</span>Batch <b>{{ strtoupper($viewer->batch) }}</b></span>
        </div>
    @endif

    @if ($viewer?->isStudent())
        {{-- Polls for newly set homework so the page refreshes the moment a teacher assigns it --}}
        <span id="hwSig" hidden data-sig="{{ $hwMine->map(fn ($h) => $h->id.'@'.$h->updated_at?->timestamp)->sort()->implode(',') }}" data-url="{{ route('my.homework.sig') }}"></span>
    @endif

    @if ($viewer?->isStudent() && $hwMine->isNotEmpty())
        @php($myHwCards = collect($cards)->filter(fn ($c) => $c['myHw'])->sortBy(fn ($c) => $c['myHw']->due_at ?? now()->addYears(5)))
        @php($newHw = $myHwCards->filter(fn ($c) => ! $c['hwDone'] && ! $c['myHw']->overdue() && $c['myHw']->updated_at?->gt(now()->subDays(2))))
        @foreach ($newHw as $c)
            <div class="hw-alert" data-key="hw-{{ $c['myHw']->id }}-{{ $c['myHw']->updated_at?->timestamp }}">
                <span class="hw-alert-ic"><span class="material-icons-outlined">notifications_active</span></span>
                <div class="hw-alert-txt">
                    <b>New homework: {{ $c['title'] }}</b>
                    <span>{{ $c['myHw']->due_at ? 'Deadline '.$c['myHw']->due_at->format('D, j M · g:i A') : 'No deadline' }}
                        @if ($c['myHw']->due_at) · <b class="hw-cd" data-due="{{ $c['myHw']->due_at->toIso8601String() }}"></b>@endif</span>
                </div>
                <a class="hw-alert-go" href="{{ route('reading.homework', $c['slug']) }}">Start now</a>
                <button type="button" class="hw-alert-x" title="Hide" aria-label="Hide">&times;</button>
            </div>
        @endforeach
        {{-- to-do first, then missed, then submitted --}}
        @php($hwRows = collect($myHwCards)->map(fn ($c) => $c + ['state' => $c['hwDone'] ? 'done' : ($c['myHw']->overdue() ? 'late' : 'open')])->sortBy(fn ($c) => ['open' => 0, 'late' => 1, 'done' => 2][$c['state']])->values())
        @php($hwCount = $hwRows->countBy('state'))
        <section class="hw-panel" aria-labelledby="hw-title">
            <div class="hw-head">
                <span class="hw-head-ic"><span class="material-icons-outlined">assignment</span></span>
                <div>
                    <h2 id="hw-title">Your homework</h2>
                    <p class="hw-sum">
                        <b>{{ $hwCount['open'] ?? 0 }}</b> to do · <b>{{ $hwCount['done'] ?? 0 }}</b> submitted
                        @if (! empty($hwCount['late'])) · <b>{{ $hwCount['late'] }}</b> missed @endif
                    </p>
                </div>
            </div>
            <div class="hw-list">
                @foreach ($hwRows as $c)
                    @php($state = $c['state'])
                    @php($link = $state !== 'late') {{-- submitted homework opens again for practice --}}
                    <{{ $link ? 'a' : 'div' }} class="hw-item {{ $state }}" @if ($link) href="{{ route('reading.homework', $c['slug']) }}" @endif>
                        <span class="hw-st" aria-hidden="true"><span class="material-icons-outlined">{{ ['open' => 'edit_note', 'done' => 'check', 'late' => 'schedule'][$state] }}</span></span>
                        <span class="hw-main">
                            <b>{{ $c['title'] }}</b>
                            <span class="hw-meta">
                                @if ($state === 'done')
                                    <span class="ok">Submitted</span><span>Your first attempt is with your teacher</span>
                                @elseif ($state === 'late')
                                    <span class="bad">Deadline passed</span><span>{{ $c['myHw']->dueLabel() }}</span>
                                @else
                                    <span>{{ $c['myHw']->dueLabel() }}</span>
                                    @if ($c['myHw']->due_at)<span class="hw-cd hw-cd-chip" data-due="{{ $c['myHw']->due_at->toIso8601String() }}"></span>@endif
                                @endif
                            </span>
                        </span>
                        @if ($state === 'open')
                            <span class="hw-act primary">Start <span class="material-icons-outlined">arrow_forward</span></span>
                        @elseif ($state === 'done')
                            <span class="hw-act ghost" title="Practice tries are not sent to your teacher"><span class="material-icons-outlined">replay</span>Practice</span>
                        @else
                            <span class="hw-act muted">Talk to your teacher</span>
                        @endif
                        @if ($c['myHw']->note)<small>{{ $c['myHw']->note }}</small>@endif
                    </{{ $link ? 'a' : 'div' }}>
                @endforeach
            </div>
        </section>
    @endif

    <div class="cards">
        @foreach ($cards as $c)
            @php($tag = $c['url'] ? 'a' : 'div')
            @php($ctl = $c['ctl'] ?? null)
            <div class="lock-wrap {{ $c['kind'] === 'start' ? 'w-start' : '' }}" @if ($ctl) data-slug="{{ $ctl['slug'] }}" data-title="{{ $c['title'] }}" data-parts="{{ json_encode($ctl['parts']) }}" @endif>
            <div class="card-box">
            <{{ $tag }} @if ($c['url']) href="{{ url($c['url']) }}" @endif @if ($c['locked']) title="Locked — your teacher will unlock this class" @endif class="card k-{{ $c['kind'] }} {{ $c['url'] ? '' : 'soon' }} {{ $c['locked'] ? 'is-locked' : '' }}" data-num="{{ $c['num'] ? sprintf('%02d', $c['num']) : '' }}">
                @if ($c['kind'] === 'start')
                    <span class="ic"><span class="material-icons-outlined">{{ $icons['start'] }}</span></span>
                    <div class="body">
                        <div><div class="eyebrow">{{ $labels['start'] }}</div><h3>{{ $c['topic'] }}</h3></div>
                        <div class="chips">@foreach (explode(' · ', $c['meta']) as $chip)@continue(! ($viewer?->isStaff()) && str_contains($chip, 'homework'))<span>{{ $chip }}</span>@endforeach</div>
                    </div>
                    <span class="go"><span class="material-icons-outlined">arrow_forward</span></span>
                @else
                    <div class="card-top">
                        <span class="ic"><span class="material-icons-outlined">{{ $icons[$c['kind']] }}</span></span>
                        @if ($labels[$c['kind']])<span class="tag">{{ $labels[$c['kind']] }}</span>@endif
                    </div>
                    <div>
                        @if ($c['num'])<div class="eyebrow">CLASS {{ sprintf('%02d', $c['num']) }}</div>@endif
                        <h3>{{ $c['topic'] }}</h3>
                        @if ($c['myHw'])<span class="hw-badge {{ $c['hwDone'] ? 'done' : ($c['myHw']->overdue() ? 'late' : '') }}" style="margin-top:8px"><span class="material-icons-outlined">{{ $c['hwDone'] ? 'check_circle' : 'home_work' }}</span>Homework · {{ $c['hwDone'] ? 'Submitted' : $c['myHw']->dueLabel() }}</span>@endif
                    </div>
                    <div class="card-foot">
                        <div class="chips">@foreach (explode(' · ', $c['meta']) as $chip)@continue(! ($viewer?->isStaff()) && str_contains($chip, 'homework'))<span>{{ $chip }}</span>@endforeach</div>
                        @if ($c['url'])
                            <span class="go"><span class="material-icons-outlined">arrow_forward</span></span>
                        @elseif ($c['locked'])
                            <span class="badge locked"><span class="material-icons-outlined" style="font-size:14px;vertical-align:-2px">lock</span> LOCKED</span>
                        @else
                            <span class="badge">COMING SOON</span>
                        @endif
                    </div>
                @endif
            </{{ $tag }}>
            @if ($ctl)
                {{-- Lock pill floats on the card, as on CD IELTS: hover shows what a click will do --}}
                <button type="button" class="unlock-btn {{ $ctl['on'] ? 'is-on' : '' }}" title="Open / close this class for batch {{ strtoupper($classBatch) }}">
                    <span class="u-locked"><span class="material-icons-outlined">lock</span>Locked</span>
                    <span class="u-unlock"><span class="material-icons-outlined">lock_open</span>Unlock</span>
                    <span class="u-unlocked"><span class="material-icons-outlined">check_circle</span>Unlocked</span>
                    <span class="u-relock"><span class="material-icons-outlined">lock</span>Lock again</span>
                </button>
            @endif
            </div>
            @if ($ctl)
                @php($total = collect($ctl['parts'])->sum('minutes'))
                @php($changed = collect($ctl['parts'])->contains(fn ($p) => $p['minutes'] !== $p['default']))
                <div class="t-ctl">
                    <div class="t-tools">
                        @if (count($ctl['parts']))
                        <button type="button" class="tool time-tool {{ $changed ? 'is-set' : '' }}" title="Set the homework time for batch {{ strtoupper($classBatch) }}"><span class="material-icons-outlined">timer</span><span class="tv">HW {{ $total }} min</span></button>
                        @endif
                        @if ($ctl['hwCount'])
                            @php($hw = $ctl['hw'])
                            <button type="button" class="tool hw-tool {{ $hw ? ($hw->overdue() ? 'is-late' : 'is-set') : '' }}" title="Set this class's homework for batch {{ strtoupper($classBatch) }}"
                                data-count="{{ $ctl['hwCount'] }}" data-assigned="{{ $hw ? 1 : 0 }}" data-due="{{ $hw?->due_at?->format('Y-m-d\TH:i') }}" data-note="{{ $hw?->note }}">
                                <span class="material-icons-outlined">home_work</span><span class="hv">{{ $hw ? ($hw->due_at ? 'HW · '.$hw->due_at->format('j M') : 'HW set') : 'Homework' }}</span></button>
                        @endif
                        @if ($module === 'reading')
                            @if ($ctl['hasKey'])
                                <button type="button" class="tool ans-tool {{ $ctl['shown'] ? 'is-set' : '' }}" title="Show the answers on every student's screen"><span class="material-icons-outlined">{{ $ctl['shown'] ? 'visibility' : 'visibility_off' }}</span><span class="av">{{ $ctl['shown'] ? 'Answers shown' : 'Show answers' }}</span></button>
                            @else
                                <span class="tool muted" title="The admin has not added the answers yet"><span class="material-icons-outlined">key_off</span>No key</span>
                            @endif
                        @endif
                    </div>
                </div>
            @endif
            </div>
        @endforeach
    </div>
</section>

@if ($canControl)
    <div class="mi-toast" id="miToast" role="status" aria-live="polite"></div>

    {{-- Teacher clicks a locked class: unlock first? --}}
    <div class="mi-modal" id="unlockAsk" role="dialog" aria-modal="true" aria-labelledby="uaTitle">
        <div class="mi-box">
            <div class="mi-box-icon"><span class="material-icons-outlined">lock</span></div>
            <h3 id="uaTitle">Unlock this class?</h3>
            <p><b id="uaName"></b> is locked for batch <b>{{ strtoupper($classBatch) }}</b>. Unlock it now so every student in the batch can open it?</p>
            <div class="mi-acts">
                <button type="button" class="b-yes">Unlock now</button>
                <button type="button" class="b-open">Just view the class</button>
                <button type="button" class="b-cancel">Cancel</button>
            </div>
        </div>
    </div>

    {{-- Homework for this batch --}}
    <div class="mi-modal" id="hwBox" role="dialog" aria-modal="true" aria-labelledby="hwTitle">
        <form class="mi-box">
            <div class="mi-box-icon" style="background:#FFF7E6;color:#D97706"><span class="material-icons-outlined">home_work</span></div>
            <h3 id="hwTitle">Set homework</h3>
            <p><b class="hw-name"></b> · <span class="hw-count"></span> · batch <b>{{ strtoupper($classBatch) }}</b></p>
            <div class="hw-form">
                <label>Deadline <span style="font-weight:400;color:var(--t-muted)">(optional)</span>
                    <input type="datetime-local" name="due_at">
                </label>
                <div class="hw-step">1. How many days?</div>
                <div class="hw-quick">
                    <button type="button" data-days="0.5">Today</button>
                    <button type="button" data-days="1">Tomorrow</button>
                    <button type="button" data-days="2">In 2 days</button>
                    <button type="button" data-days="3">In 3 days</button>
                    <button type="button" data-days="7">In a week</button>
                    <button type="button" data-days="0">No deadline</button>
                </div>
                <div class="hw-step">2. By what time?</div>
                <div class="hw-quick hw-times">
                    <button type="button" data-time="10:00">10 AM</button>
                    <button type="button" data-time="14:00">2 PM</button>
                    <button type="button" data-time="18:00">6 PM</button>
                    <button type="button" data-time="22:00" class="on">10 PM</button>
                    <button type="button" data-time="23:59">11:59 PM</button>
                </div>
                <p class="hw-when" aria-live="polite"></p>
                <label>Note for students <span style="font-weight:400;color:var(--t-muted)">(optional)</span>
                    <textarea name="note" rows="2" maxlength="500" placeholder="e.g. Do all exercises without a dictionary."></textarea>
                </label>
            </div>
            <div class="mi-acts">
                <button type="submit" class="b-yes">Assign homework</button>
                <button type="button" class="b-remove" hidden>Remove homework</button>
                <button type="button" class="b-cancel">Cancel</button>
            </div>
        </form>
    </div>

    {{-- Time for each exercise, for this batch --}}
    <div class="mi-modal" id="timeBox" role="dialog" aria-modal="true" aria-labelledby="tbTitle">
        <form class="mi-box tb">
            <div class="mi-box-icon"><span class="material-icons-outlined">timer</span></div>
            <h3 id="tbTitle">Set the homework time</h3>
            <p><b class="tb-name"></b> · batch <b>{{ strtoupper($classBatch) }}</b></p>
            <div class="tb-all"><span>Every homework</span><span class="stepper"><input type="number" min="1" max="300" class="all-in" value="10"><em>min</em></span><button type="button" class="apply">Apply to all</button></div>
            <div class="tb-rows"></div>
            <div class="tb-total"><span>Total</span><b><span class="tb-sum">0</span> min</b></div>
            <p class="tb-hint">Students already in the test get the change on their clock within 10 seconds.</p>
            <div class="mi-acts">
                <button type="submit" class="b-yes">Save time</button>
                <button type="button" class="b-reset">Back to default</button>
                <button type="button" class="b-cancel">Cancel</button>
            </div>
        </form>
    </div>
@endif
@endsection

@if ($canControl)
@push('scripts')
<script>
(function () {
    var base = @json(url('/admin/batches/'.rawurlencode($classBatch))), module = @json($module), token = @json(csrf_token());
    function post(path, body) {
        return fetch(base + path, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
            body: JSON.stringify(body)
        }).then(function (r) { if (!r.ok) throw r; return r.json(); });
    }
    function toast(msg) {
        var t = document.getElementById('miToast');
        t.textContent = msg; t.classList.add('show');
        clearTimeout(t._h); t._h = setTimeout(function () { t.classList.remove('show'); }, 2600);
    }
    function modal(el, on) { el.classList.toggle('show', on); }
    document.querySelectorAll('.mi-modal').forEach(function (m) {
        m.addEventListener('click', function (e) { if (e.target === m || e.target.closest('.b-cancel')) modal(m, false); });
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') document.querySelectorAll('.mi-modal.show').forEach(function (m) { modal(m, false); }); });

    /* ---- lock / unlock ---- */
    function setLock(wrap, next) {
        var btn = wrap.querySelector('.unlock-btn');
        btn.classList.add('is-busy');
        return post('/toggle', { module: module, test_slug: wrap.dataset.slug, unlocked: next }).then(function (j) {
            btn.classList.toggle('is-on', j.unlocked);
            if (!j.unlocked) setShown(wrap, false);
            toast((j.unlocked ? 'Unlocked' : 'Locked') + ' for batch {{ strtoupper($classBatch) }}');
        }).catch(function () { toast('Could not save. Please try again.'); })
          .finally(function () { btn.classList.remove('is-busy'); });
    }
    var ask = document.getElementById('unlockAsk'), askWrap = null;
    ask.querySelector('.b-yes').onclick = function () { modal(ask, false); if (askWrap) setLock(askWrap, true); };
    ask.querySelector('.b-open').onclick = function () { modal(ask, false); var a = askWrap && askWrap.querySelector('a.card'); if (a) location.href = a.href; };
    document.querySelectorAll('.lock-wrap[data-slug]').forEach(function (wrap) {
        wrap.querySelector('.unlock-btn').addEventListener('click', function (e) {
            e.preventDefault();
            setLock(wrap, !this.classList.contains('is-on')).then(count);
        });
        var link = wrap.querySelector('a.card');
        if (link) link.addEventListener('click', function (e) {
            if (wrap.querySelector('.unlock-btn').classList.contains('is-on')) return;
            e.preventDefault();
            askWrap = wrap;
            document.getElementById('uaName').textContent = wrap.dataset.title;
            modal(ask, true);
            ask.querySelector('.b-yes').focus();
        });
    });

    function count() {
        var a = document.getElementById('allCount'), o = document.getElementById('openCount');
        if (a) a.textContent = document.querySelectorAll('.unlock-btn').length;
        if (o) o.textContent = document.querySelectorAll('.unlock-btn.is-on').length;
    }
    count();
    document.querySelectorAll('[data-bulk]').forEach(function (b) {
        b.addEventListener('click', function () {
            var next = b.dataset.bulk === '1';
            if (!confirm((next ? 'Unlock' : 'Lock') + ' every class for batch {{ strtoupper($classBatch) }}?')) return;
            var jobs = [];
            document.querySelectorAll('.lock-wrap[data-slug]').forEach(function (w) {
                if (w.querySelector('.unlock-btn').classList.contains('is-on') !== next) jobs.push(setLock(w, next));
            });
            Promise.all(jobs).then(function () { count(); toast((next ? 'All classes unlocked' : 'All classes locked') + ' for batch {{ strtoupper($classBatch) }}'); });
        });
    });

    /* ---- homework ---- */
    var hb = document.getElementById('hwBox'), hbForm = hb.querySelector('form'), hbBtn = null;
    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function localValue(d) { return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate()) + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes()); }
    document.querySelectorAll('.hw-tool').forEach(function (b) {
        b.addEventListener('click', function () {
            hbBtn = b;
            var wrap = b.closest('.lock-wrap'), on = b.dataset.assigned === '1';
            hb.querySelector('.hw-name').textContent = wrap.dataset.title;
            hb.querySelector('.hw-count').textContent = b.dataset.count + ' homework exercise' + (b.dataset.count === '1' ? '' : 's');
            hb.querySelector('#hwTitle').textContent = on ? 'Homework is set' : 'Set homework';
            hbForm.due_at.value = b.dataset.due || '';
            hbForm.note.value = b.dataset.note || '';
            hb.querySelector('.b-yes').textContent = on ? 'Save changes' : 'Assign homework';
            hb.querySelector('.b-remove').hidden = !on;
            hwDays = null; mark('[data-days]', null); showWhen();
            modal(hb, true);
        });
    });
    // Deadline = chosen day + chosen time. The date-time box above can still be edited by hand.
    var hwDays = null, hwTime = '22:00';
    function mark(sel, btn) { hb.querySelectorAll(sel).forEach(function (x) { x.classList.toggle('on', x === btn); }); }
    function showWhen() {
        var p = hb.querySelector('.hw-when'), v = hbForm.due_at.value;
        p.textContent = v ? 'Deadline: ' + new Date(v).toLocaleString(undefined, { weekday: 'short', day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' })
                          : 'No deadline: students can hand it in any time.';
    }
    function applyDeadline() {
        if (hwDays === null) return;
        if (!hwDays) { hbForm.due_at.value = ''; return showWhen(); }
        var t = hwTime.split(':'), d = new Date();
        d.setDate(d.getDate() + (hwDays < 1 ? 0 : hwDays)); d.setHours(+t[0], +t[1], 0, 0);
        hbForm.due_at.value = localValue(d); showWhen();
    }
    hb.querySelectorAll('[data-days]').forEach(function (q) {
        q.addEventListener('click', function () { hwDays = +q.dataset.days; mark('[data-days]', q); applyDeadline(); });
    });
    hb.querySelectorAll('[data-time]').forEach(function (q) {
        q.addEventListener('click', function () { hwTime = q.dataset.time; mark('[data-time]', q); if (hwDays === null) hwDays = 1, mark('[data-days]', hb.querySelector('[data-days="1"]')); applyDeadline(); });
    });
    hbForm.due_at.addEventListener('input', function () { hwDays = null; mark('[data-days]', null); showWhen(); });
    function saveHw(assigned) {
        var wrap = hbBtn.closest('.lock-wrap');
        post('/homework', { module: module, test_slug: wrap.dataset.slug, assigned: assigned, due_at: hbForm.due_at.value || null, note: hbForm.note.value || null }).then(function (j) {
            hbBtn.dataset.assigned = j.assigned ? '1' : '0';
            hbBtn.dataset.due = j.due_at || '';
            hbBtn.dataset.note = j.note || '';
            var late = j.assigned && j.due_at && new Date(j.due_at) < new Date();
            hbBtn.classList.toggle('is-set', j.assigned && !late);
            hbBtn.classList.toggle('is-late', !!late);
            hbBtn.querySelector('.hv').textContent = j.assigned ? (j.due_at ? 'HW · ' + new Date(j.due_at).toLocaleDateString(undefined, { day: 'numeric', month: 'short' }) : 'HW set') : 'Homework';
            modal(hb, false);
            toast(j.assigned ? 'Homework set for batch {{ strtoupper($classBatch) }} · ' + j.due_label : 'Homework removed');
        }).catch(function () { toast('Could not save. Please try again.'); });
    }
    hbForm.addEventListener('submit', function (e) { e.preventDefault(); saveHw(true); });
    hb.querySelector('.b-remove').addEventListener('click', function () {
        if (confirm('Remove this homework? Students of batch {{ strtoupper($classBatch) }} will no longer see it.')) saveHw(false);
    });

    /* ---- show answers ---- */
    function setShown(wrap, on) {
        var b = wrap.querySelector('.ans-tool'); if (!b) return;
        b.classList.toggle('is-set', on);
        b.querySelector('.material-icons-outlined').textContent = on ? 'visibility' : 'visibility_off';
        b.querySelector('.av').textContent = on ? 'Answers shown' : 'Show answers';
    }
    document.querySelectorAll('.ans-tool').forEach(function (b) {
        b.addEventListener('click', function () {
            var wrap = b.closest('.lock-wrap'), next = !b.classList.contains('is-set');
            if (next && !confirm('Show the answers of "' + wrap.dataset.title + '" to every student in batch {{ strtoupper($classBatch) }}?\nStudents who have not finished will have their answers handed in automatically.')) return;
            b.disabled = true;
            post('/answers', { test_slug: wrap.dataset.slug, shown: next }).then(function (j) {
                setShown(wrap, j.shown);
                if (j.unlocked) { wrap.querySelector('.unlock-btn').classList.add('is-on'); count(); }
                toast(j.shown ? 'Answers are now showing to the batch' : 'Answers hidden');
            }).catch(function () { toast('Could not save. Please try again.'); })
              .finally(function () { b.disabled = false; });
        });
    });

    /* ---- time ---- */
    var tb = document.getElementById('timeBox'), tbForm = tb.querySelector('form'), tbWrap = null;
    function rowsFor(parts) {
        var box = tb.querySelector('.tb-rows'); box.innerHTML = '';
        Object.keys(parts).forEach(function (k) {
            var p = parts[k], row = document.createElement('div');
            row.className = 'tb-row';
            row.innerHTML = '<span class="tb-part"><b></b><small></small></span>' +
                '<span class="stepper"><button type="button" data-step="-1">−</button><input type="number" min="1" max="300"><button type="button" data-step="1">+</button><em>min</em></span>';
            row.querySelector('b').textContent = p.name;
            row.querySelector('small').textContent = (p.count ? p.count + ' questions · ' : '') + 'default ' + p.default + ' min';
            var inp = row.querySelector('input');
            inp.name = k; inp.value = p.minutes; inp.dataset.default = p.default;
            inp.classList.toggle('changed', +p.minutes !== +p.default);
            box.appendChild(row);
        });
        sum();
    }
    function sum() {
        var t = 0;
        tb.querySelectorAll('.tb-rows input').forEach(function (i) { t += +i.value || 0; });
        tb.querySelector('.tb-sum').textContent = t;
    }
    tb.querySelector('.apply').addEventListener('click', function () {
        var v = Math.max(1, Math.min(300, +tb.querySelector('.all-in').value || 1));
        tb.querySelectorAll('.tb-rows input').forEach(function (i) { i.value = v; i.classList.toggle('changed', v !== +i.dataset.default); });
        sum();
    });
    document.querySelectorAll('.time-tool').forEach(function (b) {
        b.addEventListener('click', function () {
            tbWrap = b.closest('.lock-wrap');
            tb.querySelector('.tb-name').textContent = tbWrap.dataset.title;
            rowsFor(JSON.parse(tbWrap.dataset.parts));
            modal(tb, true);
        });
    });
    tb.addEventListener('click', function (e) {
        var s = e.target.closest('[data-step]'); if (!s) return;
        var inp = s.parentNode.querySelector('input');
        inp.value = Math.max(1, Math.min(300, (+inp.value || 0) + +s.dataset.step));
        inp.classList.toggle('changed', +inp.value !== +inp.dataset.default);
        sum();
    });
    tb.addEventListener('input', function (e) { if (e.target.dataset.default) { e.target.classList.toggle('changed', +e.target.value !== +e.target.dataset.default); sum(); } });
    function saveTime(reset) {
        var minutes = {};
        tb.querySelectorAll('.tb-rows input').forEach(function (i) { minutes[i.name] = +i.value; });
        post('/times', { module: module, test_slug: tbWrap.dataset.slug, minutes: minutes, reset: reset }).then(function (j) {
            var parts = JSON.parse(tbWrap.dataset.parts), total = 0, changed = false;
            Object.keys(parts).forEach(function (k) { parts[k].minutes = +j.times[k]; total += parts[k].minutes; if (parts[k].minutes !== +parts[k].default) changed = true; });
            tbWrap.dataset.parts = JSON.stringify(parts);
            var tool = tbWrap.querySelector('.time-tool');
            tool.querySelector('.tv').textContent = 'HW ' + total + ' min';
            tool.classList.toggle('is-set', changed);
            modal(tb, false);
            toast('Time saved for batch {{ strtoupper($classBatch) }}');
        }).catch(function () { toast('Could not save. Please try again.'); });
    }
    tbForm.addEventListener('submit', function (e) { e.preventDefault(); saveTime(false); });
    tb.querySelector('.b-reset').addEventListener('click', function () { saveTime(true); });
})();
</script>
@endpush
@endif

@if ($viewer?->isStudent())
@push('scripts')
<script>
(function () {
    /* Live countdown to each homework deadline */
    function fmt(ms) {
        if (ms <= 0) return 'Time is up';
        var s = Math.floor(ms / 1000), d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
        function p(n) { return (n < 10 ? '0' : '') + n; }
        return (d ? d + 'd ' : '') + p(h) + 'h ' + p(m) + 'm ' + p(sec) + 's left';
    }
    var els = document.querySelectorAll('.hw-cd[data-due]');
    function tick() {
        var now = Date.now();
        els.forEach(function (el) {
            var left = new Date(el.dataset.due).getTime() - now;
            el.textContent = '⏳ ' + fmt(left);
            el.classList.toggle('soon', left < 864e5);
        });
    }
    if (els.length) { tick(); setInterval(tick, 1000); }

    /* "Hide" on a notification is remembered on this device */
    document.querySelectorAll('.hw-alert').forEach(function (a) {
        var k = 'seen-' + a.dataset.key;
        try { if (localStorage.getItem(k)) { a.remove(); return; } } catch (e) {}
        a.querySelector('.hw-alert-x').addEventListener('click', function () {
            try { localStorage.setItem(k, '1'); } catch (e) {}
            a.remove();
        });
    });

    /* Coming back with the browser's Back button shows a cached copy of this page (homework just handed in
       would still be listed): load it fresh instead. */
    window.addEventListener('pageshow', function (e) {
        var nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
        if (e.persisted || (nav && nav.type === 'back_forward')) location.reload();
    });

    /* New homework from the teacher shows up without a manual refresh */
    var sig = document.getElementById('hwSig');
    if (sig) setInterval(function () {
        if (document.hidden) return;
        fetch(sig.dataset.url, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) { if (j && j.sig !== sig.dataset.sig) location.reload(); })
            .catch(function () {});
    }, 20000);
})();
</script>
@endpush
@endif
