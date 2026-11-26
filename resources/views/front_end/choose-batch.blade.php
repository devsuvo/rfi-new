@extends('front_end.layout.app')

@section('title', 'Choose batch')

@push('styles')
<style>
    .cb { padding: 64px 0 96px; text-align: center; }
    .cb .pill-w { display: inline-block; background: #E8EFFF; color: var(--hp-blue); font-weight: 700; font-size: 13px; padding: 7px 16px; border-radius: 999px; }
    .cb h1 { margin: 16px 0 8px; font-size: clamp(28px, 4vw, 42px); font-weight: 800; letter-spacing: -.02em; color: var(--hp-navy); }
    .cb h1 span { color: var(--hp-blue); }
    .cb .lead { color: var(--t-muted); margin: 0 0 32px; }
    .cb-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 260px), 1fr)); gap: 16px; max-width: 900px; margin: 0 auto; text-align: left; }
    .cb-grid button { position: relative; display: flex; align-items: center; gap: 14px; padding: 20px; background: #fff; border: 1.5px solid var(--t-line); border-radius: 20px; cursor: pointer; font: inherit; text-align: left; box-shadow: var(--shadow-sm); transition: transform .25s var(--ease), border-color .25s, box-shadow .25s; animation: rise .5s var(--ease) both; }
    .cb-grid button:hover { transform: translateY(-4px); border-color: var(--hp-blue); box-shadow: var(--shadow-lg); }
    .cb-grid button.current { border-color: var(--hp-blue); background: #F5F8FF; }
    .b-icon { width: 48px; height: 48px; border-radius: 14px; background: #E8EFFF; color: var(--hp-blue); display: grid; place-items: center; flex: none; }
    .b-name { display: block; font-weight: 800; font-size: 18px; color: var(--hp-navy); letter-spacing: .02em; }
    .b-meta { color: var(--t-muted); font-size: 13px; }
    .b-go { margin-left: auto; color: var(--hp-navy); }
    .b-tag { position: absolute; top: -10px; right: 14px; background: var(--hp-blue); color: #fff; font-size: 10px; font-weight: 800; letter-spacing: 1px; padding: 3px 9px; border-radius: 999px; }
</style>
@endpush

@section('content')
<section class="cb wrap">
    <span class="pill-w">Welcome, {{ auth()->user()->name }}</span>
    <h1>Choose your <span>batch</span></h1>
    <p class="lead">You teach {{ count($batches) }} batches. Pick the one you are taking class with now.</p>
    <form method="POST" action="{{ route('batch.choose.store') }}" class="cb-grid">
        @csrf
        <input type="hidden" name="back" value="{{ request('back') }}">
        @foreach ($batches as $b)
            <button type="submit" name="batch" value="{{ $b }}" class="{{ $b === $current ? 'current' : '' }}">
                @if ($b === $current)<span class="b-tag">CURRENT</span>@endif
                <span class="b-icon"><span class="material-icons-outlined">groups</span></span>
                <span><span class="b-name">{{ strtoupper($b) }}</span><span class="b-meta">{{ $counts[$b] ?? 0 }} students</span></span>
                <span class="material-icons-outlined b-go">arrow_forward</span>
            </button>
        @endforeach
    </form>
</section>
@endsection
