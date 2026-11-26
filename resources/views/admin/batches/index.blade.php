@extends('admin.layout')
@section('title', 'Batches')

@push('styles')
<style>
    .b-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px; }
    .b-card { display: flex; flex-direction: column; gap: 12px; margin: 0; transition: transform .2s, border-color .2s; }
    .b-card:hover { transform: translateY(-3px); border-color: var(--blue); }
    .b-card h3 { margin: 0; font-size: 18px; display: flex; align-items: center; gap: 8px; }
    .b-meta { display: flex; gap: 14px; color: var(--muted); font-size: 13px; }
    .b-meta b { color: var(--ink); }
    .prog { height: 7px; border-radius: 99px; background: var(--line); overflow: hidden; }
    .prog i { display: block; height: 100%; background: var(--green); }
</style>
@endpush

@section('content')
<div class="page-head"><div>
    <h1>{{ auth()->user()->isAdmin() ? 'Batches & Class Locks' : 'My Batches' }}</h1>
    <div class="sub">Open a batch to see its students and lock / unlock classes for it.</div>
</div></div>

@if (! $batches)
    <div class="card empty">
        @if (auth()->user()->isAdmin())
            No batches yet. Register a student with a batch name to create one.
        @else
            No batch has been assigned to you yet. Please ask the admin.
        @endif
    </div>
@else
<div class="b-grid">
    @foreach ($batches as $b)
        @php($open = $unlocked[$b] ?? 0)
        <a class="card b-card" href="{{ route('admin.batches.show', $b) }}">
            <h3><span class="material-icons-outlined" style="color:var(--blue)">groups</span>{{ $b }}</h3>
            <div class="b-meta"><span><b>{{ $students[$b] ?? 0 }}</b> students</span><span><b>{{ $open }}</b> / {{ $totalClasses }} unlocked</span></div>
            <div class="prog"><i style="width: {{ $totalClasses ? round($open / $totalClasses * 100) : 0 }}%"></i></div>
            @if (auth()->user()->isAdmin())
                <div class="muted" style="font-size:12px">Teacher: {{ ($teachers[$b] ?? collect())->map(fn ($t) => $t->teacher?->name)->filter()->join(', ') ?: 'not assigned' }}</div>
            @endif
        </a>
    @endforeach
</div>
@endif
@endsection
