@extends('admin.layout')
@section('title', 'Analytics')

@push('styles')
<style>
    /* Series colours (validated for colour-blind separation, light + dark) */
    .dash { --s-read: #08909F; --s-write: #E2263C; }
    :root[data-theme="dark"] .dash { --s-read: #1BA6B8; --s-write: #E85A5A; }

    /* Hero */
    .d-hero { position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;
        padding: 26px 28px; margin-bottom: 20px; border-radius: 22px; color: #fff;
        background: radial-gradient(120% 140% at 100% 0%, rgba(56,189,248,.35) 0%, transparent 55%), linear-gradient(135deg, #1E3A8A 0%, #2563EB 60%, #0EA5E9 100%);
        box-shadow: 0 18px 40px -22px rgba(37,99,235,.7); }
    .d-hero::after { content: ''; position: absolute; inset: 0; background-image: radial-gradient(rgba(255,255,255,.12) 1px, transparent 1px); background-size: 18px 18px; mask-image: linear-gradient(90deg, transparent 40%, #000); pointer-events: none; }
    .d-hero h1 { margin: 0 0 4px; font-size: 26px; letter-spacing: -.02em; }
    .d-hero p { margin: 0; opacity: .85; font-size: 14px; }
    .d-hero .pills { display: flex; gap: 10px; flex-wrap: wrap; position: relative; z-index: 1; }
    .d-hero .pill { display: flex; flex-direction: column; padding: 10px 16px; border-radius: 14px; background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); backdrop-filter: blur(6px); min-width: 108px; }
    .d-hero .pill b { font-size: 20px; line-height: 1.2; }
    .d-hero .pill span { font-size: 11.5px; opacity: .85; text-transform: uppercase; letter-spacing: .5px; }
    .d-hero .pill .up { color: #BBF7D0; } .d-hero .pill .down { color: #FECACA; }

    /* KPI tiles */
    .kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; margin-bottom: 20px; }
    .kpi { position: relative; overflow: hidden; display: flex; flex-direction: column; gap: 14px; padding: 20px; border-radius: 18px; background: var(--panel); border: 1px solid var(--line); box-shadow: var(--shadow); color: inherit; text-decoration: none; transition: transform .2s, box-shadow .2s, border-color .2s; }
    a.kpi:hover { transform: translateY(-2px); border-color: var(--tone); }
    .kpi::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; background: var(--tone); }
    .kpi .top { display: flex; align-items: center; justify-content: space-between; }
    .kpi .ic { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; background: var(--soft); color: var(--tone); }
    .kpi .lab { font-size: 13px; color: var(--muted); font-weight: 600; }
    .kpi .v { font-size: 30px; font-weight: 800; line-height: 1; letter-spacing: -.02em; color: var(--ink); }
    .kpi .sub { font-size: 12.5px; color: var(--muted); }
    .kpi .meter { height: 6px; border-radius: 6px; background: var(--line); overflow: hidden; }
    .kpi .meter i { display: block; height: 100%; border-radius: 6px; background: var(--tone); }

    /* Two-column area */
    .d-cols { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 20px; }
    .d-cols > .card { margin-bottom: 20px; }
    .c-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 6px; }
    .c-head h3 { margin: 0; font-size: 16px; }
    .c-head .muted { font-size: 13px; }
    .legend { display: flex; gap: 14px; font-size: 12.5px; color: var(--muted); }
    .legend span { display: inline-flex; align-items: center; gap: 6px; }
    .legend i { width: 10px; height: 10px; border-radius: 3px; }

    /* Stacked bar chart */
    .chart { position: relative; display: grid; grid-template-columns: 28px 1fr; gap: 6px; margin-top: 14px; }
    .y-axis { display: flex; flex-direction: column; justify-content: space-between; height: 200px; font-size: 11px; color: var(--muted); text-align: right; }
    .plot { position: relative; height: 200px; display: flex; align-items: flex-end; gap: 6px; border-bottom: 1px solid var(--line); }
    .plot .grid-l { position: absolute; left: 0; right: 0; border-top: 1px dashed var(--line); opacity: .7; pointer-events: none; }
    .col { position: relative; flex: 1 1 0; min-width: 0; height: 100%; display: flex; flex-direction: column-reverse; align-items: center; gap: 2px; cursor: default; }
    .col .seg { width: 70%; max-width: 30px; min-width: 6px; flex: none; }
    .col .seg.r { background: var(--s-read); border-radius: 0 0 2px 2px; }
    .col .seg.w { background: var(--s-write); }
    .col .seg:last-child { border-radius: 4px 4px 0 0; }
    .col .seg:only-child { border-radius: 4px 4px 2px 2px; }
    .col:hover .seg { filter: brightness(1.1); }
    .col .tip { position: absolute; bottom: calc(100% + 8px); left: 50%; transform: translateX(-50%); z-index: 3; white-space: nowrap; padding: 8px 10px; border-radius: 10px; background: var(--ink); color: var(--panel); font-size: 12px; line-height: 1.5; opacity: 0; pointer-events: none; transition: opacity .15s; }
    .col .tip i { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin-right: 5px; }
    .col:hover .tip { opacity: 1; }
    .col.no-data::after { content: ''; width: 60%; max-width: 30px; height: 3px; border-radius: 3px; background: var(--line); }
    .x-axis { display: flex; gap: 6px; margin-left: 34px; margin-top: 6px; }
    .x-axis span { flex: 1; text-align: center; font-size: 11px; color: var(--muted); }
    .x-axis span.today { color: var(--blue); font-weight: 700; }

    /* Band distribution */
    .bands { display: grid; gap: 12px; margin-top: 16px; }
    .band-row { display: grid; grid-template-columns: 64px 1fr 34px; align-items: center; gap: 10px; font-size: 13px; }
    .band-row .lbl { color: var(--muted); font-weight: 600; }
    .band-row .track { height: 12px; border-radius: 6px; background: var(--panel-2); overflow: hidden; }
    .band-row .track i { display: block; height: 100%; border-radius: 0 4px 4px 0; background: var(--blue); }
    .band-row b { text-align: right; color: var(--ink); }
    .avg-band { display: flex; align-items: baseline; gap: 8px; margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--line); }
    .avg-band b { font-size: 34px; font-weight: 800; color: var(--ink); letter-spacing: -.02em; }
    .avg-band span { color: var(--muted); font-size: 13px; }

    /* Homework */
    .hw-row { border: 1px solid var(--line); border-radius: 14px; margin-bottom: 10px; overflow: hidden; background: var(--panel); }
    .hw-row summary { list-style: none; cursor: pointer; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 10px 16px; align-items: center; padding: 14px 16px; }
    .hw-row summary::-webkit-details-marker { display: none; }
    .hw-row[open] summary { border-bottom: 1px solid var(--line); background: var(--panel-2); }
    .hw-row .ttl b { display: block; font-size: 15px; }
    .hw-row .ttl small { color: var(--muted); font-size: 12.5px; }
    .hw-nums { display: flex; gap: 8px; flex-wrap: wrap; justify-content: flex-end; }
    .hw-num { display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
    .hw-num .material-icons-outlined { font-size: 15px; }
    .hw-num.ok { background: var(--green-soft); color: var(--green); }
    .hw-num.no { background: var(--red-soft); color: var(--red); }
    .hw-num.late { background: var(--amber-soft); color: var(--amber); }
    .hw-bar { grid-column: 1 / -1; height: 6px; border-radius: 6px; background: var(--red-soft); overflow: hidden; }
    .hw-bar i { display: block; height: 100%; background: var(--green); border-radius: 6px; }
    .hw-cols { display: grid; grid-template-columns: 1fr 1fr; }
    .hw-cols > div { padding: 12px 16px; }
    .hw-cols > div + div { border-left: 1px solid var(--line); }
    .hw-cols h4 { margin: 0 0 8px; font-size: 12px; text-transform: uppercase; letter-spacing: .5px; }
    .hw-cols ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 6px; font-size: 14px; }
    .hw-cols li { display: flex; justify-content: space-between; gap: 8px; }
    .hw-cols li span { color: var(--muted); font-size: 12.5px; }

    /* Latest submissions */
    .who { display: flex; align-items: center; gap: 10px; }
    .ava { width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; font-size: 13px; font-weight: 800; color: #fff; flex: none; }
    .score { display: inline-flex; align-items: center; gap: 8px; }
    .score .chip { padding: 3px 9px; border-radius: 999px; background: var(--blue-soft); color: var(--blue); font-weight: 700; font-size: 12.5px; }
    .dot-m { display: inline-flex; align-items: center; gap: 6px; font-weight: 600; font-size: 13px; }
    .dot-m i { width: 8px; height: 8px; border-radius: 50%; }
    .empty-state { text-align: center; padding: 26px 10px; color: var(--muted); }
    .empty-state .material-icons-outlined { font-size: 34px; opacity: .5; display: block; margin-bottom: 6px; }

    /* Daily motivation (teachers) */
    .d-quote { display: inline-flex; align-items: flex-start; gap: 8px; max-width: 640px; margin-top: 12px; padding: 9px 14px; border-radius: 12px;
        background: rgba(255,255,255,.14); border: 1px solid rgba(255,255,255,.22); font-size: 13.5px; font-style: italic; line-height: 1.45; }
    .d-quote .material-icons-outlined { font-size: 18px; color: #FDE68A; font-style: normal; flex: none; }

    /* Hero actions */
    .d-acts { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 16px; }
    .d-acts a { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 999px; background: #fff; color: #1E3A8A; font-weight: 700; font-size: 13px; text-decoration: none; box-shadow: 0 6px 14px -8px rgba(0,0,0,.4); transition: transform .2s; }
    .d-acts a.ghost { background: rgba(255,255,255,.14); color: #fff; border: 1px solid rgba(255,255,255,.3); box-shadow: none; }
    .d-acts a:hover { transform: translateY(-1px); }
    .d-acts .material-icons-outlined { font-size: 17px; }

    /* Sparkline inside a KPI */
    .spark { width: 100%; height: 34px; display: block; margin-top: -4px; }
    .spark path.l { fill: none; stroke: var(--tone); stroke-width: 2; stroke-linejoin: round; stroke-linecap: round; }
    .spark path.a { fill: var(--tone); opacity: .12; }
    .spark circle { fill: var(--tone); stroke: var(--panel); stroke-width: 2; }

    /* Third row */
    .d-cols3 { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.2fr) minmax(0, 1fr); gap: 20px; }
    .d-cols3 > .card { margin-bottom: 20px; }
    .ring-wrap { display: flex; align-items: center; gap: 20px; margin-top: 18px; }
    .ring { position: relative; width: 120px; height: 120px; flex: none; } .ring svg { width: 120px; height: 120px; }
    .ring svg { transform: rotate(-90deg); }
    .ring circle { fill: none; stroke-width: 12; }
    .ring .bg { stroke: var(--red-soft); }
    .ring .fg { stroke: var(--green); stroke-linecap: round; transition: stroke-dashoffset .8s; }
    .ring .mid { position: absolute; inset: 0; display: grid; place-items: center; text-align: center; }
    .ring .mid b { display: block; font-size: 28px; font-weight: 800; color: var(--ink); line-height: 1; }
    .ring .mid span { font-size: 11.5px; color: var(--muted); }
    .ring-legend { display: grid; gap: 10px; font-size: 13.5px; }
    .ring-legend { flex: 1; min-width: 0; }
    .ring-legend div { display: flex; align-items: center; gap: 8px; white-space: nowrap; }
    .ring-legend i { width: 10px; height: 10px; border-radius: 3px; flex: none; }
    .ring-legend b { margin-left: auto; color: var(--ink); padding-left: 10px; }
    .bt { display: grid; gap: 4px; margin-top: 14px; }
    .bt-row { display: grid; grid-template-columns: 38px minmax(0, 1fr) auto; gap: 12px; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--line); }
    .bt-row:last-child { border-bottom: 0; }
    .bt-ic { width: 38px; height: 38px; border-radius: 12px; display: grid; place-items: center; background: var(--blue-soft); color: var(--blue); font-weight: 800; font-size: 13px; }
    .bt-row b { display: block; font-size: 14px; color: var(--ink); }
    .bt-row small { color: var(--muted); font-size: 12.5px; }
    .bt-row .avg { text-align: right; display: flex; flex-direction: column; line-height: 1.2; }
    .bt-row .avg b { font-size: 18px; }
    .lead { display: grid; gap: 4px; margin-top: 14px; }
    .lead-row { display: grid; grid-template-columns: 26px 34px minmax(0, 1fr) auto; gap: 10px; align-items: center; padding: 8px 0; }
    .lead-row .rk { font-weight: 800; color: var(--muted); text-align: center; }
    .lead-row:nth-child(1) .rk { color: #D4A017; } .lead-row:nth-child(2) .rk { color: #9AA3B5; } .lead-row:nth-child(3) .rk { color: #C27C4E; }
    .lead-row b { display: block; font-size: 14px; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .lead-row small { color: var(--muted); font-size: 12px; }
    .lead-row .chip { padding: 4px 10px; border-radius: 999px; background: var(--blue-soft); color: var(--blue); font-weight: 800; font-size: 13px; }

    /* Polish */
    .kpi .sub { margin-top: auto; }
    .kpi .v small.tr { font-size: 12.5px; font-weight: 700; margin-left: 6px; padding: 2px 7px; border-radius: 999px; vertical-align: middle; }
    .kpi .v small.tr.up { background: var(--green-soft); color: var(--green); } .kpi .v small.tr.down { background: var(--red-soft); color: var(--red); }
    .col .seg.r { background: linear-gradient(180deg, var(--s-read), color-mix(in srgb, var(--s-read) 70%, #000 0%)); border-radius: 6px 6px 2px 2px !important; box-shadow: 0 6px 14px -8px var(--s-read); transition: height .6s cubic-bezier(.2,.8,.2,1), filter .2s; }
    .col .seg.w { border-radius: 6px 6px 2px 2px !important; }
    .col .val { font-size: 11px; font-weight: 800; color: var(--ink); line-height: 1; margin-bottom: 4px; }
    .col:hover { background: color-mix(in srgb, var(--blue) 6%, transparent); border-radius: 8px; }
    .band-row .track i { background: var(--bt, var(--blue)); transition: width .6s; }
    .band-row b small { color: var(--muted); font-weight: 600; font-size: 11px; margin-left: 4px; }
    .band-row { grid-template-columns: 64px 1fr 58px; }
    .avg-band .scale { flex: 1; height: 8px; border-radius: 6px; background: linear-gradient(90deg, var(--red), var(--amber), var(--green)); position: relative; margin-left: 10px; align-self: center; }
    .avg-band .scale i { position: absolute; top: 50%; width: 14px; height: 14px; border-radius: 50%; background: var(--panel); border: 3px solid var(--ink); transform: translate(-50%, -50%); }
    .kpi .bandbar { height: 6px; border-radius: 6px; background: var(--line); overflow: hidden; } .kpi .bandbar i { display: block; height: 100%; background: var(--tone); border-radius: 6px; }
    .card { transition: box-shadow .2s; } .card:hover { box-shadow: 0 14px 34px -22px rgba(15,23,42,.35); }
    @media (max-width: 1100px) { .d-cols3 { grid-template-columns: 1fr 1fr; } .d-cols3 > .card:last-child { grid-column: 1 / -1; } }
    @media (max-width: 640px) { .d-cols3 { grid-template-columns: 1fr; } .ring-wrap { flex-direction: column; align-items: flex-start; } }
    @media (max-width: 1100px) { .kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); } .d-cols { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .kpis { grid-template-columns: 1fr; }
        .d-hero { padding: 20px; } .d-hero .pill { min-width: 0; flex: 1; }
        .x-axis span:nth-child(even) { visibility: hidden; }
        .hw-cols { grid-template-columns: 1fr; } .hw-cols > div + div { border-left: 0; border-top: 1px solid var(--line); }
        .hw-row summary { grid-template-columns: 1fr; } .hw-nums { justify-content: flex-start; }
    }
</style>
@endpush

@section('content')
@php
    $me = auth()->user();
    $hour = now()->hour;
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
    $delta = $thisWeek - $lastWeek;
    $hwDone = $homework->sum(fn ($h) => count($h->done));
    $hwTotal = $homework->sum('total');
    $hwRate = $hwTotal ? round($hwDone / $hwTotal * 100) : null;
    $isW = $course === 'writing';
    $cName = $isW ? 'Writing' : 'Reading';
    $marked = $writingResults - $pendingWriting;
    $markRate = $writingResults ? round($marked / $writingResults * 100) : null;
    $chartMax = max(1, $chart->max(fn ($c) => $c[$course]));
    $step = max(1, (int) ceil($chartMax / 4));
    $axisMax = $step * 4;
    $bandMax = max(1, $bandRows->max('n'));
    $avaColors = ['#2563EB', '#0891B2', '#7C3AED', '#DB2777', '#EA580C', '#16A34A'];
    // 14-day sparkline path for one module (viewBox 0 0 140 34)
    $spark = function (string $key) use ($chart) {
        $vals = $chart->pluck($key)->values();
        $max = max(1, $vals->max());
        $pts = $vals->map(fn ($v, $i) => [round($i * 140 / max(1, $vals->count() - 1), 1), round(30 - $v / $max * 26, 1)]);
        $line = 'M'.$pts->map(fn ($p) => $p[0].' '.$p[1])->implode(' L');

        return ['line' => $line, 'area' => $line.' L140 34 L0 34 Z', 'last' => $pts->last()];
    };
    $sp = $spark($course);
@endphp

<div class="dash">
    <section class="d-hero">
        <div style="position:relative;z-index:1">
            <h1>{{ $greet }}, {{ \Illuminate\Support\Str::of($me->name)->before(' ') }} 👋</h1>
            <p>{{ now()->format('l, j F Y') }} · {{ $cName }} for IELTS overview @if ($me->isTeacher() && $currentBatch) · Batch <b>{{ strtoupper($currentBatch) }}</b>
                · <a href="{{ route('batch.choose', ['back' => '/admin']) }}" style="color:#fff;text-decoration:underline">Change batch</a>
            @endif</p>
            @if ($me->isTeacher())
                @php
                    // A warm wish that changes every day and fits the time of day (same all day for one teacher).
                    $first = \Illuminate\Support\Str::of($me->name)->before(' ');
                    $wishes = [
                        'morning' => [
                            'Wishing you a bright and peaceful morning, :name. May today bring you lots of smiles ☀️',
                            'A fresh morning, a fresh start. Have a wonderful day ahead, :name 🌼',
                            'Grab your tea or coffee and enjoy a calm start, :name. Today is going to be a good day ☕',
                            'Good things are coming your way today, :name. Have a lovely morning 🌤️',
                            'May your day be as kind and cheerful as you are, :name 💛',
                            'Rise and shine, :name! Wishing you energy, joy and good news today 🌻',
                            'Here\'s to a happy, easy-going day, :name. You deserve it 🌸',
                        ],
                        'afternoon' => [
                            'Hope your day is going beautifully, :name. Take a short break and smile 😊',
                            'Halfway through the day! Don\'t forget to drink some water, :name 💧',
                            'Wishing you a relaxed and pleasant afternoon, :name 🌿',
                            'You\'re doing great today, :name. Enjoy the rest of your day 🌞',
                            'A little reminder: you are appreciated, :name. Have a lovely afternoon 💐',
                            'Hope something nice made you smile today, :name 🌈',
                            'Take a deep breath and enjoy the moment, :name. The day is yours 🍃',
                        ],
                        'evening' => [
                            'Hope you had a wonderful day, :name. Time to relax and recharge 🌙',
                            'Thank you for all your hard work today, :name. Have a peaceful evening ✨',
                            'Wishing you a calm, cosy evening with the people you love, :name 🏡',
                            'You did well today, :name. Rest well and sweet dreams 🌟',
                            'May your evening be as peaceful as a quiet sunset, :name 🌅',
                            'Put your feet up and enjoy your evening, :name. You\'ve earned it ☕',
                            'Good night in advance, :name. Tomorrow will be even better 🌛',
                        ],
                    ];
                    $part = $hour < 12 ? 'morning' : ($hour < 17 ? 'afternoon' : 'evening');
                    $list = $wishes[$part];
                    $quote = str_replace(':name', $first, $list[(now()->dayOfYear + $me->id) % count($list)]);
                @endphp
                <div class="d-quote"><span class="material-icons-outlined">favorite</span><span>{{ $quote }}</span></div>
            @endif
            <div class="d-acts">
                <a href="{{ route($course) }}"><span class="material-icons-outlined">lock_open</span>Open classes</a>
                @if ($isW)
                    <a class="ghost" href="{{ route('admin.writing.index', ['status' => 'pending']) }}"><span class="material-icons-outlined">rate_review</span>Mark writing</a>
                @else
                    <a class="ghost" href="{{ route('admin.reading.results') }}"><span class="material-icons-outlined">fact_check</span>Results</a>
                    @if ($me->isAdmin())<a class="ghost" href="{{ route('admin.reading.answers') }}"><span class="material-icons-outlined">key</span>Answer keys</a>@endif
                @endif
                <a class="ghost" href="{{ route('admin.batches.index') }}"><span class="material-icons-outlined">groups</span>Batches</a>
            </div>
        </div>
        <div class="pills">
            <div class="pill"><span>Today</span><b>{{ $today }}</b></div>
            <div class="pill"><span>This week</span><b>{{ $thisWeek }}
                @if ($thisWeek || $lastWeek)<small class="{{ $delta >= 0 ? 'up' : 'down' }}" style="font-size:12px">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}</small>@endif</b></div>
            @if ($isW)
                <div class="pill"><span>Feedback sent</span><b>{{ $markRate !== null ? $markRate.'%' : '—' }}</b></div>
            @else
                <div class="pill"><span>Homework done</span><b>{{ $hwRate !== null ? $hwRate.'%' : 'None set' }}</b></div>
            @endif
        </div>
    </section>

    <div class="kpis">
        <div class="kpi" style="--tone:var(--blue);--soft:var(--blue-soft)">
            <div class="top"><span class="lab">Students</span><span class="ic"><span class="material-icons-outlined">groups</span></span></div>
            <div class="v">{{ $students }}</div>
            <div class="sub">{{ $batches }} batch{{ $batches === 1 ? '' : 'es' }}{{ $me->isAdmin() ? ' · '.$teachers.' teacher'.($teachers === 1 ? '' : 's') : '' }}</div>
        </div>
        @if (! $isW)
        <a class="kpi" href="{{ route('admin.reading.results') }}" style="--tone:var(--s-read);--soft:var(--cyan-soft)">
            <div class="top"><span class="lab">Reading results</span><span class="ic"><span class="material-icons-outlined">menu_book</span></span></div>
            <div class="v">{{ $readingResults }}@if ($thisWeek || $lastWeek)<small class="tr {{ $delta >= 0 ? 'up' : 'down' }}" title="This week vs last week">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }} this wk</small>@endif</div>
            <svg class="spark" viewBox="0 0 140 34" preserveAspectRatio="none" aria-hidden="true"><path class="a" d="{{ $sp['area'] }}"/><path class="l" d="{{ $sp['line'] }}" vector-effect="non-scaling-stroke"/></svg>
            <div class="sub">{{ $keys }} answer key{{ $keys === 1 ? '' : 's' }} saved</div>
        </a>
        <div class="kpi" style="--tone:var(--green);--soft:var(--green-soft)">
            <div class="top"><span class="lab">Average correct</span><span class="ic"><span class="material-icons-outlined">task_alt</span></span></div>
            <div class="v">{{ $avgBand !== null ? $avgBand.'%' : '—' }}</div>
            <div class="bandbar" title="{{ $avgBand ?? 0 }}% correct"><i style="width: {{ $avgBand ?? 0 }}%"></i></div>
            <div class="sub">{{ $avgScore ? 'Avg '.$avgScore.' correct · ' : '' }}{{ $bandCount }} result{{ $bandCount === 1 ? '' : 's' }}</div>
        </div>
        <div class="kpi" style="--tone:var(--amber);--soft:var(--amber-soft)">
            <div class="top"><span class="lab">Homework handed in</span><span class="ic"><span class="material-icons-outlined">home_work</span></span></div>
            <div class="v">{{ $hwDone }}<small style="font-size:16px;color:var(--muted);font-weight:600"> / {{ $hwTotal }}</small></div>
            <div class="meter" title="{{ $hwDone }} of {{ $hwTotal }} handed in"><i style="width: {{ $hwRate ?? 0 }}%"></i></div>
            <div class="sub">{{ $homework->count() ? $homework->count().' homework set · '.($hwRate ?? 0).'% done' : 'No homework set yet' }}</div>
        </div>
        @else
        <a class="kpi" href="{{ route('admin.writing.index') }}" style="--tone:var(--s-write);--soft:var(--red-soft)">
            <div class="top"><span class="lab">Writing submissions</span><span class="ic"><span class="material-icons-outlined">edit_note</span></span></div>
            <div class="v">{{ $writingResults }}</div>
            <svg class="spark" viewBox="0 0 140 34" preserveAspectRatio="none" aria-hidden="true"><path class="a" d="{{ $sp['area'] }}"/><path class="l" d="{{ $sp['line'] }}" vector-effect="non-scaling-stroke"/></svg>
            <div class="meter" title="{{ $marked }} of {{ $writingResults }} marked"><i style="width: {{ $markRate ?? 0 }}%"></i></div>
            <div class="sub">{{ $marked }} of {{ $writingResults }} feedback sent</div>
        </a>
        <a class="kpi" href="{{ route('admin.writing.index', ['status' => 'pending']) }}" style="--tone:var(--amber);--soft:var(--amber-soft)">
            <div class="top"><span class="lab">Waiting for feedback</span><span class="ic"><span class="material-icons-outlined">pending_actions</span></span></div>
            <div class="v">{{ $pendingWriting }}</div>
            <div class="sub">{{ $pendingWriting ? 'Writing tasks to mark' : 'All caught up ✓' }}</div>
        </a>
        <div class="kpi" style="--tone:var(--green);--soft:var(--green-soft)">
            <div class="top"><span class="lab">Average band</span><span class="ic"><span class="material-icons-outlined">insights</span></span></div>
            <div class="v">{{ $avgBand ? number_format($avgBand, 1) : '—' }}</div>
            <div class="sub">{{ $bandCount }} marked task{{ $bandCount === 1 ? '' : 's' }}</div>
        </div>
        @endif
    </div>

    <div class="d-cols">
        <div class="card">
            <div class="c-head">
                <div><h3>{{ $cName }} submissions — last 14 days</h3><div class="muted">{{ $chart->sum($course) }} in total · hover a day for details</div></div>
            </div>
            <div class="chart" role="img" aria-label="{{ $cName }} submissions per day for the last 14 days">
                <div class="y-axis">@for ($v = $axisMax; $v >= 0; $v -= $step)<span>{{ $v }}</span>@endfor</div>
                <div class="plot">
                    @for ($g = 1; $g <= 4; $g++)<div class="grid-l" style="bottom: {{ $g * 25 }}%"></div>@endfor
                    @foreach ($chart as $c)
                        @php($sum = $c[$course])
                        <div class="col {{ $sum ? '' : 'no-data' }}">
                            @if ($sum)<div class="seg {{ $isW ? 'w' : 'r' }}" style="height: {{ round($sum / $axisMax * 180) }}px"></div><div class="val">{{ $sum }}</div>@endif
                            <div class="tip"><b>{{ \Illuminate\Support\Carbon::parse($c['day'])->format('D, j M') }}</b><br>
                                {{ $sum }} {{ strtolower($cName) }} submission{{ $sum === 1 ? '' : 's' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="x-axis">@foreach ($chart as $c)<span class="{{ $c['day'] === now()->toDateString() ? 'today' : '' }}">{{ \Illuminate\Support\Carbon::parse($c['day'])->format('j M') }}</span>@endforeach</div>
        </div>

        <div class="card">
            <div class="c-head"><div><h3>Correct answers</h3><div class="muted">How many results fall in each % range · {{ $bandCount }} result{{ $bandCount === 1 ? '' : 's' }}</div></div></div>
            @if ($bandCount)
                <div class="bands">
                    @foreach ($bandRows as $b)
                        <div class="band-row" title="{{ $b['label'] }}: {{ $b['n'] }} result(s)">
                            <span class="lbl">{{ $b['label'] }}</span>
                            <span class="track"><i style="width: {{ $b['n'] / $bandMax * 100 }}%; --bt: {{ ['var(--red)', 'var(--amber)', 'var(--blue)', 'var(--cyan, #0891B2)', 'var(--green)'][$loop->index] ?? 'var(--blue)' }}"></i></span>
                            <b>{{ $b['n'] }}<small>{{ round($b['n'] / $bandCount * 100) }}%</small></b>
                        </div>
                    @endforeach
                </div>
                <div class="avg-band"><b>{{ $avgBand }}%</b><span>average correct</span><span class="scale" title="{{ $avgBand }}%"><i style="left: {{ $avgBand }}%"></i></span></div>
            @else
                <div class="empty-state"><span class="material-icons-outlined">insights</span>{{ $isW ? 'Bands appear once writing tasks are marked.' : 'Appears once reading results are marked with an answer key.' }}</div>
            @endif
        </div>
    </div>

    <div class="d-cols3">
        @if ($isW)
        <div class="card">
            <div class="c-head"><div><h3>Marking progress</h3><div class="muted">{{ $writingResults }} writing submission{{ $writingResults === 1 ? '' : 's' }}</div></div></div>
            @php($circ = 2 * M_PI * 54)
            <div class="ring-wrap">
                <div class="ring" role="img" aria-label="{{ $markRate ?? 0 }}% of writing feedback sent">
                    <svg width="132" height="132" viewBox="0 0 132 132"><circle class="bg" cx="66" cy="66" r="54"/>
                        <circle class="fg" cx="66" cy="66" r="54" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ * (1 - ($markRate ?? 0) / 100) }}" @if (! $markRate) style="display:none" @endif/></svg>
                    <div class="mid"><div><b>{{ $markRate !== null ? $markRate.'%' : '—' }}</b><span>feedback sent</span></div></div>
                </div>
                <div class="ring-legend">
                    <div><i style="background:var(--green)"></i>Feedback sent<b>{{ $marked }}</b></div>
                    <div><i style="background:var(--red)"></i>Waiting<b>{{ $pendingWriting }}</b></div>
                </div>
            </div>
        </div>
        @else
        <div class="card">
            <div class="c-head"><div><h3>Homework completion</h3><div class="muted">{{ $homework->count() }} homework set</div></div></div>
            @php($circ = 2 * M_PI * 54)
            <div class="ring-wrap">
                <div class="ring" role="img" aria-label="{{ $hwRate ?? 0 }}% of homework handed in">
                    <svg width="132" height="132" viewBox="0 0 132 132"><circle class="bg" cx="66" cy="66" r="54"/>
                        <circle class="fg" cx="66" cy="66" r="54" stroke-dasharray="{{ $circ }}" stroke-dashoffset="{{ $circ * (1 - ($hwRate ?? 0) / 100) }}" @if (! $hwRate) style="display:none" @endif/></svg>
                    <div class="mid"><div><b>{{ $hwRate !== null ? $hwRate.'%' : '—' }}</b><span>handed in</span></div></div>
                </div>
                <div class="ring-legend">
                    <div><i style="background:var(--green)"></i>Submitted<b>{{ $hwDone }}</b></div>
                    <div><i style="background:var(--red)"></i>Not submitted<b>{{ $hwTotal - $hwDone }}</b></div>
                    <div><i style="background:var(--amber)"></i>Late<b>{{ $homework->sum('late') }}</b></div>
                </div>
            </div>
        </div>
        @endif

        <div class="card">
            <div class="c-head"><div><h3>{{ $isW ? 'Top writers' : 'Top readers' }}</h3><div class="muted">Most correct answers overall</div></div></div>
            <div class="lead">
                @forelse ($topStudents as $i => $t)
                    <div class="lead-row">
                        <span class="rk">{{ $i < 3 ? ['🥇', '🥈', '🥉'][$i] : $i + 1 }}</span>
                        <span class="ava" style="background: {{ $avaColors[crc32($t->user->name) % count($avaColors)] }}">{{ mb_strtoupper(mb_substr($t->user->name, 0, 1)) }}</span>
                        <div style="min-width:0"><b>{{ $t->user->name }}</b><small>{{ $t->user->batch ? strtoupper($t->user->batch).' · ' : '' }}{{ $t->correct }}/{{ $t->total }} correct · {{ $t->n }} test{{ $t->n === 1 ? '' : 's' }}</small></div>
                        <span class="chip">{{ $t->avg }}%</span>
                    </div>
                @empty
                    <div class="empty-state"><span class="material-icons-outlined">emoji_events</span>Appears once {{ strtolower($cName) }} results are marked.</div>
                @endforelse
            </div>
        </div>
    </div>

    @unless ($isW)
    <div class="card">
        <div class="c-head">
            <div><h3>Homework</h3><div class="muted">Who has handed in each homework · click a row to see names</div></div>
            @if ($hwTotal)<div class="legend"><span><i style="background:var(--green)"></i>Submitted {{ $hwDone }}</span><span><i style="background:var(--red)"></i>Not submitted {{ $hwTotal - $hwDone }}</span></div>@endif
        </div>
        <div style="margin-top:14px">
        @forelse ($homework as $h)
            <details class="hw-row">
                <summary>
                    <div class="ttl"><b>{{ $h->title }}</b>
                        <small>Batch {{ strtoupper($h->hw->batch) }} · {{ $h->hw->dueLabel() }}{{ $h->hw->overdue() ? ' (closed)' : '' }}</small></div>
                    <div class="hw-nums">
                        <span class="hw-num ok"><span class="material-icons-outlined">check_circle</span>{{ count($h->done) }} submitted</span>
                        <span class="hw-num no"><span class="material-icons-outlined">cancel</span>{{ count($h->missing) }} not submitted</span>
                        @if ($h->late)<span class="hw-num late"><span class="material-icons-outlined">schedule</span>{{ $h->late }} late</span>@endif
                    </div>
                    <div class="hw-bar"><i style="width: {{ $h->total ? round(count($h->done) / $h->total * 100) : 0 }}%"></i></div>
                </summary>
                <div class="hw-cols">
                    <div>
                        <h4 style="color:var(--green)">Submitted ({{ count($h->done) }})</h4>
                        <ul>
                            @forelse ($h->done as $d)
                                <li><a href="{{ route('admin.reading.show', $d['result']) }}">{{ $d['student']->name }}</a>
                                    <span>{{ $d['result']->total ? $d['result']->score.'/'.$d['result']->total.' · ' : '' }}{{ $d['result']->created_at->format('j M, g:i A') }}{{ $d['result']->late ? ' · late' : '' }}</span></li>
                            @empty
                                <li class="muted">Nobody yet.</li>
                            @endforelse
                        </ul>
                    </div>
                    <div>
                        <h4 style="color:var(--red)">Not submitted ({{ count($h->missing) }})</h4>
                        <ul>
                            @forelse ($h->missing as $s)
                                <li>{{ $s->name }}</li>
                            @empty
                                <li class="muted">Everyone has handed it in.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </details>
        @empty
            <div class="empty-state"><span class="material-icons-outlined">home_work</span>No homework has been set yet. Teachers set it from the Reading class page with the Homework button.</div>
        @endforelse
        </div>
    </div>
    @endunless

    <div class="card">
        <div class="c-head"><div><h3>Latest {{ strtolower($cName) }} submissions</h3><div class="muted">The 8 most recent hand-ins</div></div></div>
        <div class="tbl-wrap" style="margin-top:10px"><table class="t">
            <tr><th>Student</th><th>Test</th><th>{{ $isW ? 'Band' : 'Score' }}</th><th>When</th><th></th></tr>
            @forelse ($latest as $res)
                @php($name = $res->user->name ?? 'Deleted user')
                <tr>
                    <td><span class="who"><span class="ava" style="background: {{ $avaColors[crc32($name) % count($avaColors)] }}">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span><b>{{ $name }}</b></span></td>
                    <td>{{ $res->testTitle() }}@if ($res->kind === 'homework') <span class="badge" style="background:var(--amber-soft);color:var(--amber)">Homework{{ $res->late ? ' · late' : '' }}</span>@endif</td>
                    <td>
                        @if ($res->total)<span class="score"><b>{{ $res->score }}/{{ $res->total }}</b><span class="chip">{{ round($res->score / $res->total * 100) }}%</span></span>
                        @elseif ($res->band !== null)<span class="score"><span class="chip">Band {{ $res->band }}</span></span>
                        @else <span class="muted">Not marked</span>@endif
                    </td>
                    <td class="muted" title="{{ $res->created_at->format('j M Y, g:i A') }}">{{ $res->created_at->diffForHumans() }}</td>
                    <td class="row-acts"><a class="btn sm" href="{{ $res->module === 'writing' ? route('admin.writing.show', $res) : route('admin.reading.show', $res) }}">Open</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="empty">No submissions yet.</td></tr>
            @endforelse
        </table></div>
    </div>
</div>
@endsection
