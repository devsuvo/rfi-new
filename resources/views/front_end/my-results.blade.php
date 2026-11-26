@extends('front_end.layout.app')

@section('title', 'My Results')

@push('styles')
<style>
    .mr { padding: 48px 0 96px; }
    .mr h1 { font-size: clamp(28px, 4vw, 40px); color: var(--hp-navy); margin: 0 0 6px; letter-spacing: -.02em; }
    .mr .sub { color: var(--t-muted); margin: 0 0 28px; }
    .mr-list { display: grid; gap: 12px; }
    .mr-row { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid var(--t-line); border-radius: 18px; padding: 16px 20px; box-shadow: var(--shadow-sm); transition: transform .2s, box-shadow .2s; }
    a.mr-row:hover { transform: translateY(-2px); box-shadow: var(--shadow-lg); }
    .mr-ic { width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; flex: none; }
    .mr-ic.reading { background: #E3F6F8; color: #0AA3B5; } .mr-ic.writing { background: #FDE9EC; color: #E2263C; }
    .mr-body { flex: 1; min-width: 0; }
    .mr-body b { display: block; color: var(--hp-navy); }
    .mr-body small { color: var(--t-muted); }
    .mr-score { text-align: right; font-weight: 700; color: var(--hp-navy); }
    .mr-score small { display: block; font-weight: 500; color: var(--t-muted); font-size: 12px; }
    .pill-wait { background: #FEF3E2; color: #B45309; font-size: 12px; font-weight: 600; padding: 5px 12px; border-radius: 999px; }
    .mr-nav { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
    .mr-nav a { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--t-muted); background: #fff; padding: 7px 16px 7px 10px; border-radius: 999px; border: 1px solid var(--t-line); transition: color .2s, border-color .2s, transform .2s; }
    .mr-nav a:hover { color: var(--hp-navy); border-color: var(--tone, #0AA3B5); transform: translateY(-1px); }
    .mr-nav a.main { color: var(--hp-navy); }
    .mr-nav .material-icons-outlined { font-size: 18px; }
    .empty { background: #fff; border: 1px dashed var(--t-line); border-radius: 18px; padding: 40px; text-align: center; color: var(--t-muted); }
</style>
@endpush

@section('content')
<section class="mr wrap">
    @php($backTo = $course ?? null)
    <nav class="mr-nav" aria-label="Back">
        @if ($backTo && Route::has($backTo))
            <a class="main" href="{{ route($backTo) }}" style="--tone: {{ $backTo === 'writing' ? '#E2263C' : '#0AA3B5' }}"><span class="material-icons-outlined">arrow_back</span>Back to {{ \App\Models\User::COURSES[$backTo] ?? ucfirst($backTo) }}</a>
        @endif
        <a href="{{ route('home') }}"><span class="material-icons-outlined">home</span>Home</a>
    </nav>
    <h1>My Results</h1>
    <p class="sub">@if (($course ?? null) === 'reading') Your Reading for IELTS results. Each test is marked right away. @elseif (($course ?? null) === 'writing') Your Writing for IELTS results. Your band and feedback appear once your teacher sends them. @else Reading is marked right away. Writing shows your band and feedback once your teacher sends it. @endif</p>

    <div class="mr-list">
        @forelse ($results as $r)
            @php($ready = $r->module === 'reading' ? $r->total : $r->feedback_sent_at)
            <{{ $ready ? 'a' : 'div' }} class="mr-row" @if ($ready) href="{{ route('my.results.show', $r) }}" @endif>
                <span class="mr-ic {{ $r->module }}"><span class="material-icons-outlined">{{ $r->module === 'writing' ? 'edit_note' : 'menu_book' }}</span></span>
                <div class="mr-body"><b>{{ $r->testTitle() }}</b><small>{{ ucfirst($r->module) }} · {{ $r->created_at->format('d M Y, h:i A') }}</small></div>
                <div class="mr-score">
                    @if ($r->module === 'reading' && $r->total)
                        {{ $r->score }} / {{ $r->total }}<small>Band {{ $r->band }}</small>
                    @elseif ($r->module === 'writing' && $r->feedback_sent_at)
                        Band {{ $r->band }}<small>Feedback ready</small>
                    @else
                        <span class="pill-wait">{{ $r->module === 'writing' ? 'Waiting for teacher' : 'Being marked' }}</span>
                    @endif
                </div>
            </{{ $ready ? 'a' : 'div' }}>
        @empty
            <div class="empty">You have not finished any test yet.</div>
        @endforelse
    </div>
</section>
@endsection
