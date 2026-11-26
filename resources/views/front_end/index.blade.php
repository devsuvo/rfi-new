@extends('front_end.layout.app')

@section('title', 'Home')

@push('styles')
<style>
    /* Home — Reading for IELTS (light look like CD IELTS) */
    .hp-hero { padding: 24px 0 0; }
    .hp-hero .wrap { display: grid; grid-template-columns: 1.05fr 1fr; align-items: center; gap: 40px; }
    .hp-pill { display: inline-flex; align-items: center; gap: 8px; padding: 7px 16px; border-radius: 999px; background: #EEF3FE; border: 1px solid #DFE8FB; color: #0B1F5C; font-size: 11.5px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; }
    .hp-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: #22C55E; animation: hpPulse 2s ease-out infinite; }
    @keyframes hpPulse { 0% { box-shadow: 0 0 0 0 rgba(34,197,94,.45); } 70% { box-shadow: 0 0 0 7px rgba(34,197,94,0); } 100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); } }
    .hp-title { position: relative; margin: 14px 0 12px; font-size: clamp(40px, 4.6vw, 70px); font-weight: 800; line-height: 1; letter-spacing: -.035em; color: #0B1F5C; }
    .hp-title .hp-l { display: inline-block; font-weight: inherit; animation: hpDrop .7s cubic-bezier(.3,1.6,.5,1) backwards; animation-delay: calc(var(--l) * 60ms + 150ms); }
    @keyframes hpDrop { from { opacity: 0; transform: translateY(-.6em) scale(.8); } to { opacity: 1; transform: none; } }
    .hp-prem { position: relative; display: inline-block; animation: hpPop .9s cubic-bezier(.2,.9,.3,1.2) .75s backwards; }
    @keyframes hpPop { from { opacity: 0; transform: translateX(-.4em) scale(.9); filter: blur(8px); } to { opacity: 1; transform: none; filter: none; } }
    .hp-prem-text { color: transparent; -webkit-background-clip: text; background-clip: text; background-image: linear-gradient(90deg, #1D5BFF, #6D4BFF, #22A6FF, #1D5BFF); background-size: 300% 100%; animation: hpFlow 6s linear infinite; }
    @keyframes hpFlow { to { background-position: 300% 0; } }
    .hp-swoosh { position: absolute; left: 2%; bottom: -.18em; width: 96%; height: .28em; overflow: visible; }
    .hp-swoosh path { fill: none; stroke: #E2263C; stroke-width: 5; stroke-linecap: round; stroke-dasharray: 320; stroke-dashoffset: 320; animation: hpDraw .9s ease-out 1.5s forwards; }
    @keyframes hpDraw { to { stroke-dashoffset: 0; } }
    .hp-spark { position: absolute; right: -.42em; top: -.12em; width: .32em; height: .32em; fill: #F5A524; opacity: 0; animation: hpTwinkle 2.4s ease-in-out 2.2s infinite; }
    @keyframes hpTwinkle { 0%, 100% { opacity: 0; transform: scale(.4) rotate(0); } 40%, 60% { opacity: 1; transform: scale(1) rotate(45deg); } }
    .hp-lead { max-width: 520px; margin: 0 0 20px; color: #3B4766; font-size: 19px; line-height: 1.45; }
    .hp-tagline { display: flex; align-items: center; gap: 14px; margin-top: 20px; color: #7B8499; font-size: 11.5px; font-weight: 500; letter-spacing: .26em; text-transform: uppercase; }
    .hp-tagline::before { content: ''; width: 56px; height: 2px; background: #E2263C; }
    .hp-batch { display: inline-flex; align-items: center; gap: 16px; margin-top: 28px; padding: 14px 22px 14px 14px; border-radius: 20px; background: #fff; border: 1px solid #E3E9F8; box-shadow: 0 20px 40px -28px rgba(29,91,255,.55); }
    .hp-batch-icon { flex: none; width: 56px; height: 56px; border-radius: 16px; display: grid; place-items: center; color: #fff; background: linear-gradient(135deg, #1D5BFF, #5B8CFF); box-shadow: 0 10px 22px -10px rgba(29,91,255,.8); }
    .hp-batch-icon .material-icons-outlined { font-size: 30px; }
    .hp-batch-text { display: flex; flex-direction: column; line-height: 1.1; }
    .hp-batch-label { font-size: 11.5px; font-weight: 700; letter-spacing: .18em; text-transform: uppercase; color: #7B8499; }
    .hp-batch-name { margin-top: 4px; font-size: clamp(26px, 2.6vw, 36px); font-weight: 800; letter-spacing: -.02em; color: #0B1F5C; }
    .hp-photo { position: relative; margin: 0; justify-self: end; width: 100%; max-width: 480px; }
    .hp-photo img { display: block; width: 100%; height: auto;
        -webkit-mask-image: linear-gradient(to right, transparent 0, #000 9%, #000 94%, transparent 100%), linear-gradient(to bottom, transparent 0, #000 8%, #000 82%, transparent 100%); -webkit-mask-composite: source-in;
        mask-image: linear-gradient(to right, transparent 0, #000 9%, #000 94%, transparent 100%), linear-gradient(to bottom, transparent 0, #000 8%, #000 82%, transparent 100%); mask-composite: intersect; }

    /* ---------- Animated hero illustration ---------- */
    .hp-art { --a-teal: #0AA3B5; --a-teal-soft: #E3F6F8; --a-navy: #0B1F5C; --a-red: #E2263C; --a-card: #fff; --a-line: #E6EBF3; --a-text: #DDE3ED; --a-muted: #7B8499; --a-gold: #F5B83D;
        position: relative; margin: 0; justify-self: end; width: 100%; max-width: 480px; aspect-ratio: 1 / .9; }
    .ha-blob { position: absolute; inset: 6% 4% 4% 10%; border-radius: 46% 54% 52% 48% / 50% 44% 56% 50%;
        background: radial-gradient(circle at 30% 30%, #D6F2F6, transparent 60%), linear-gradient(140deg, #E3F6F8, #E8EDFF);
        animation: haBlob 14s ease-in-out infinite; }
    .ha-dots { position: absolute; left: 2%; top: 10%; width: 74px; height: 74px; opacity: .55;
        background-image: radial-gradient(#9FB3D1 1.6px, transparent 1.8px); background-size: 14px 14px; animation: haFloat 7s ease-in-out infinite; }
    .ha-card { position: absolute; left: 14%; top: 14%; width: 64%; padding: 16px 16px 18px; border-radius: 18px; background: var(--a-card);
        border: 1px solid var(--a-line); box-shadow: 0 30px 60px -28px rgba(11,31,92,.45); animation: haFloat 6s ease-in-out infinite; }
    .ha-top { display: flex; align-items: center; gap: 5px; margin-bottom: 14px; }
    .ha-top i { width: 8px; height: 8px; border-radius: 50%; background: #FF6B6B; } .ha-top i:nth-child(2) { background: #FFC24B; } .ha-top i:nth-child(3) { background: #3DD68C; }
    .ha-top span { margin-left: auto; font-size: 11px; font-weight: 700; color: var(--a-teal); background: var(--a-teal-soft); padding: 3px 9px; border-radius: 999px; }
    .ha-title { width: 58%; height: 12px; border-radius: 6px; background: var(--a-navy); opacity: .85; margin-bottom: 12px; }
    .ha-lines p { position: relative; width: var(--w); height: 7px; margin: 0 0 8px; border-radius: 4px; background: var(--a-text); overflow: hidden; }
    .ha-lines p.hl::after { content: ''; position: absolute; inset: 0; border-radius: 4px; background: rgba(245,184,61,.75); transform-origin: left; animation: haMark 4.8s ease-in-out infinite; }
    .ha-qs { margin-top: 14px; display: grid; gap: 8px; }
    .ha-q { display: flex; align-items: center; gap: 8px; padding: 7px 8px; border-radius: 10px; background: #F6F8FB; border: 1px solid var(--a-line); }
    .ha-q b { width: 20px; height: 20px; flex: none; display: grid; place-items: center; border-radius: 6px; font-size: 11px; color: var(--a-teal); border: 1.5px solid #CFD6E2; background: var(--a-card);
        animation: haFill 4.8s step-end infinite; animation-delay: var(--d); }
    .ha-q span { width: var(--w); height: 6px; border-radius: 3px; background: var(--a-text); }
    .ha-q em { margin-left: auto; width: 18px; height: 18px; display: grid; place-items: center; border-radius: 50%; font-style: normal; font-size: 11px; font-weight: 800; color: #fff; background: #22C55E;
        transform: scale(0); animation: haTick 4.8s cubic-bezier(.3,1.6,.5,1) infinite; animation-delay: var(--d); }
    .ha-chip { position: absolute; display: flex; align-items: center; gap: 10px; padding: 10px 14px 10px 10px; border-radius: 16px; background: var(--a-card);
        border: 1px solid var(--a-line); box-shadow: 0 18px 40px -22px rgba(11,31,92,.5); }
    .ha-chip small { display: block; font-size: 10.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--a-muted); }
    .ha-chip b { font-size: 17px; color: var(--a-navy); }
    .ha-chip > .material-icons-outlined { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 11px; font-size: 21px; }
    .ha-band { right: 0; top: 8%; animation: haFloat 5.5s ease-in-out infinite .6s; }
    .ha-band svg { width: 44px; height: 44px; transform: rotate(-90deg); }
    .ha-band .trk { fill: none; stroke: var(--a-teal-soft); stroke-width: 5; }
    .ha-band .bar { fill: none; stroke: var(--a-teal); stroke-width: 5; stroke-linecap: round; stroke-dasharray: 113; stroke-dashoffset: 113; animation: haRing 4.8s ease-out infinite; }
    .ha-time { left: 0; bottom: 3%; animation: haFloat 6.5s ease-in-out infinite 1.2s; }
    .ha-time > .material-icons-outlined { background: #FDE9EC; color: var(--a-red); animation: haPulse 1.6s ease-in-out infinite; }
    .ha-ok { right: 2%; bottom: 6%; animation: haFloat 6s ease-in-out infinite 2s; }
    .ha-ok > .material-icons-outlined { background: #E8F7EE; color: #16A34A; }
    .ha-book { position: absolute; right: 14%; top: 42%; font-size: 30px; color: var(--a-teal); opacity: .9; animation: haSpin 9s ease-in-out infinite; }
    .ha-spark { position: absolute; width: 14px; height: 14px; background: var(--a-gold);
        clip-path: polygon(50% 0, 62% 38%, 100% 50%, 62% 62%, 50% 100%, 38% 62%, 0 50%, 38% 38%); animation: haTwinkle 2.8s ease-in-out infinite; }
    .ha-spark.s1 { left: 8%; top: 4%; } .ha-spark.s2 { right: 30%; top: 2%; width: 10px; height: 10px; animation-delay: .9s; background: var(--a-red); }
    .ha-spark.s3 { left: 40%; bottom: 2%; width: 12px; height: 12px; animation-delay: 1.7s; background: var(--a-teal); }
    @keyframes haFloat { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }
    @keyframes haBlob { 0%, 100% { border-radius: 46% 54% 52% 48% / 50% 44% 56% 50%; transform: rotate(0); } 50% { border-radius: 56% 44% 42% 58% / 44% 56% 44% 56%; transform: rotate(8deg); } }
    @keyframes haMark { 0% { transform: scaleX(0); } 35%, 80% { transform: scaleX(1); } 100% { transform: scaleX(1); opacity: 0; } }
    @keyframes haTick { 0%, 20% { transform: scale(0); } 30%, 85% { transform: scale(1); } 100% { transform: scale(0); } }
    @keyframes haFill { 0% { background: var(--a-card); color: var(--a-teal); border-color: #CFD6E2; } 25% { background: var(--a-teal); color: #fff; border-color: var(--a-teal); } 90% { background: var(--a-card); color: var(--a-teal); border-color: #CFD6E2; } }
    @keyframes haRing { 0% { stroke-dashoffset: 113; } 60%, 100% { stroke-dashoffset: 17; } }
    @keyframes haPulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.08); } }
    @keyframes haSpin { 0%, 100% { transform: rotate(-10deg) translateY(0); } 50% { transform: rotate(10deg) translateY(-8px); } }
    @keyframes haTwinkle { 0%, 100% { transform: scale(.6) rotate(0); opacity: .5; } 50% { transform: scale(1.15) rotate(45deg); opacity: 1; } }
    :root[data-theme="dark"] .hp-art { --a-card: #151C31; --a-line: #26304A; --a-text: #2A3552; --a-navy: #E7ECF7; --a-teal-soft: #11313A; --a-muted: #94A0BC; }
    :root[data-theme="dark"] .ha-blob { background: radial-gradient(circle at 30% 30%, #12404A, transparent 60%), linear-gradient(140deg, #10283A, #1A2140); }
    :root[data-theme="dark"] .ha-q { background: #111829; }
    :root[data-theme="dark"] .ha-time > .material-icons-outlined { background: #3A1D25; }
    :root[data-theme="dark"] .ha-ok > .material-icons-outlined { background: #12301F; }
    @media (max-width: 560px) {
        .hp-tagline { letter-spacing: .14em; font-size: 10.5px; }
        .hp-tagline::before { width: 32px; flex: none; }
        .hp-art { max-width: 100%; min-width: 0; }
        .ha-card { left: 8%; width: 74%; padding: 12px; }
        .ha-chip { gap: 7px; padding: 7px 10px 7px 7px; border-radius: 13px; }
        .ha-chip b { font-size: 14px; }
        .ha-chip small { font-size: 9px; }
        .ha-chip > .material-icons-outlined, .ha-band svg { width: 30px; height: 30px; font-size: 17px; }
        .ha-book { display: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hp-art *, .hp-art { animation: none !important; }
        .ha-q em { transform: scale(1); }
        .ha-band .bar { stroke-dashoffset: 17; }
    }

    .hp-modules { padding: 26px 0 36px; }
    .hp-modules-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .hp-modules-head h2 { margin: 0; color: #0B1F5C; font-size: 32px; font-weight: 800; letter-spacing: -.02em; }
    .hp-modules-head p { margin: 0; color: #5B6B86; font-size: 15px; }
    .modules { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
    .btns { position: relative; display: flex; flex-direction: column; align-items: flex-start; justify-content: space-between; gap: 28px; min-height: 190px; padding: 22px 22px 20px; background: #fff; color: #0B1B3F; border: 1px solid #E4E9F2; border-radius: 18px; overflow: hidden; box-shadow: 0 24px 50px -30px rgba(11,27,63,.45); transition: transform .35s cubic-bezier(.2,.7,.2,1), box-shadow .35s ease, border-color .25s ease; }
    .btns::before { content: ''; position: absolute; right: -46px; top: -46px; width: 130px; height: 130px; border-radius: 50%; background: var(--soft); transition: transform .35s ease; }
    .btns::after { content: ''; position: absolute; top: 0; left: -60%; width: 40%; height: 100%; background: linear-gradient(100deg, transparent, rgba(255,255,255,.75), transparent); transform: skewX(-18deg); pointer-events: none; }
    .btns:hover { transform: translateY(-6px); border-color: var(--tone); box-shadow: 0 26px 50px -24px rgba(11,27,63,.45); }
    .btns:hover::before { transform: scale(1.25); }
    .btns:hover::after { left: 130%; transition: left .75s ease; }
    .btns .num { position: relative; font-size: 13px; font-weight: 700; color: #5B6B86; letter-spacing: .5px; }
    .btns .icon { position: relative; width: 48px; height: 48px; display: grid; place-items: center; border-radius: 14px; background: var(--soft); color: var(--tone); transition: transform .35s cubic-bezier(.3,1.6,.5,1), background .25s, color .25s; }
    .btns .icon .material-icons-outlined { font-size: 26px; }
    .btns:hover .icon { transform: rotate(-8deg) scale(1.1); background: var(--tone); color: #fff; }
    .btns .bottom { position: relative; width: 100%; display: flex; align-items: center; justify-content: space-between; }
    .btns .label { font-size: 21px; font-weight: 800; letter-spacing: -.3px; }
    .btns .go { width: 36px; height: 36px; display: grid; place-items: center; border-radius: 50%; background: var(--soft); color: var(--tone); transition: background .25s, color .25s, transform .25s; }
    .btns .go .material-icons-outlined { font-size: 20px; }
    .btns:hover .go { background: var(--tone); color: #fff; transform: translateX(3px); }
    .reveal { animation: hpRise .7s cubic-bezier(.2,.7,.2,1) backwards; animation-delay: calc(var(--i, 0) * 90ms + 100ms); }
    @keyframes hpRise { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: none; } }

        @media (max-width: 900px) {
        .hp-hero .wrap { grid-template-columns: minmax(0, 1fr); }
        .hp-hero .wrap > * { min-width: 0; }
        .hp-photo, .hp-art { justify-self: center; max-width: 520px; }
        .hp-modules-head { flex-direction: column; align-items: flex-start; }
    }
    @media (max-width: 560px) { .modules { grid-template-columns: 1fr; } .hp-batch { gap: 12px; padding: 12px 16px 12px 12px; } .hp-batch-icon { width: 46px; height: 46px; } }
</style>
@endpush

@section('content')
@php
    $homeUser = auth()->user();
    $homeBatch = $homeUser?->isStudent() ? $homeUser->batch : null;
    $mods = [
        ['key' => 'reading', 'label' => 'Reading', 'icon' => 'menu_book', 'tone' => '#0AA3B5', 'soft' => '#E3F6F8'],
        ['key' => 'writing', 'label' => 'Writing', 'icon' => 'edit_note', 'tone' => '#E2263C', 'soft' => '#FDE9EC'],
    ];
    $mods = array_values(array_filter($mods, fn ($m) => Route::has($m['key']) && (! $homeUser || $homeUser->canUseCourse($m['key']))));
@endphp
<section class="hp-hero" id="overview">
    <div class="wrap">
        <div class="hp-copy">
            <span class="hp-pill reveal">IELTS Learning Portal</span>
            <h1 class="hp-title" aria-label="Reading for IELTS"><span aria-hidden="true"><b class="hp-l" style="--l: 0">R</b><b class="hp-l" style="--l: 1">e</b><b class="hp-l" style="--l: 2">a</b><b class="hp-l" style="--l: 3">d</b><b class="hp-l" style="--l: 4">i</b><b class="hp-l" style="--l: 5">n</b><b class="hp-l" style="--l: 6">g</b> <b class="hp-l" style="--l: 8">f</b><b class="hp-l" style="--l: 9">o</b><b class="hp-l" style="--l: 10">r</b></span> <span class="hp-prem" aria-hidden="true"><span class="hp-prem-text">IELTS</span><svg class="hp-swoosh" viewBox="0 0 300 24" preserveAspectRatio="none"><path d="M4 16 C 70 4, 150 4, 296 12"/></svg><svg class="hp-spark" viewBox="0 0 24 24"><path d="M12 0 L14.5 9.5 L24 12 L14.5 14.5 L12 24 L9.5 14.5 L0 12 L9.5 9.5 Z"/></svg></span></h1>
            <p class="hp-lead reveal" style="--i: 2">Build your IELTS reading confidence with focused, class-by-class practice.</p>
            <div class="hp-tagline reveal" style="--i: 4">Practice today. A brighter tomorrow.</div>
            @if ($homeBatch)
                <div class="hp-batch reveal" style="--i: 5">
                    <span class="hp-batch-icon"><span class="material-icons-outlined">groups</span></span>
                    <span class="hp-batch-text"><span class="hp-batch-label">Your batch</span><span class="hp-batch-name">{{ mb_strtoupper($homeBatch) }}</span></span>
                </div>
            @endif
        </div>
        {{-- Animated illustration: a reading test being worked through (highlight sweeps the passage, answers get ticked) --}}
        <figure class="hp-art reveal" style="--i: 2" role="img" aria-label="Animated illustration of an IELTS reading test being answered, with band score, timer and correct answers">
            <div class="ha-blob"></div>
            <div class="ha-dots"></div>

            <div class="ha-card">
                <div class="ha-top"><i></i><i></i><i></i><span>Reading · Passage 1</span></div>
                <div class="ha-title"></div>
                <div class="ha-lines">
                    <p style="--w: 96%"></p><p class="hl" style="--w: 88%"></p><p style="--w: 92%"></p><p style="--w: 70%"></p><p style="--w: 84%"></p>
                </div>
                <div class="ha-qs">
                    <div class="ha-q" style="--d: 0s"><b>1</b><span style="--w: 62%"></span><em>✓</em></div>
                    <div class="ha-q" style="--d: 1.2s"><b>2</b><span style="--w: 48%"></span><em>✓</em></div>
                    <div class="ha-q" style="--d: 2.4s"><b>3</b><span style="--w: 56%"></span><em>✓</em></div>
                </div>
            </div>

            <div class="ha-chip ha-band">
                <svg viewBox="0 0 44 44" aria-hidden="true"><circle cx="22" cy="22" r="18" class="trk"/><circle cx="22" cy="22" r="18" class="bar"/></svg>
                <div><small>Band</small><b>7.5</b></div>
            </div>
            <div class="ha-chip ha-time"><span class="material-icons-outlined">timer</span><div><small>Time left</small><b>18:42</b></div></div>
            <div class="ha-chip ha-ok"><span class="material-icons-outlined">task_alt</span><div><small>Correct</small><b>12 / 13</b></div></div>
            <span class="ha-spark s1"></span><span class="ha-spark s2"></span><span class="ha-spark s3"></span>
            <span class="ha-book material-icons-outlined">menu_book</span>
        </figure>
    </div>
</section>

<section class="hp-modules" id="modules">
    <div class="wrap">
        <div class="hp-modules-head">
            <h2>Choose your practice module</h2>
            <p>{{ count($mods) === 1 ? 'Your course. One complete preparation path.' : (count($mods) === 2 ? 'Two' : count($mods)).' skills. One complete preparation path.' }}</p>
        </div>
        <div class="modules">
            @foreach ($mods as $m)
                <a href="{{ route($m['key']) }}" class="btns reveal" style="--i: {{ $loop->index + 3 }}; --tone: {{ $m['tone'] }}; --soft: {{ $m['soft'] }}">
                    <span class="num">{{ sprintf('%02d', $loop->iteration) }}</span>
                    <span class="icon"><span class="material-icons-outlined">{{ $m['icon'] }}</span></span>
                    <span class="bottom"><span class="label">{{ $m['label'] }}</span><span class="go"><span class="material-icons-outlined">arrow_forward</span></span></span>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection
