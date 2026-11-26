@extends('admin.layout')
@section('title', 'Batch '.$batch)

@push('styles')
<style>
    .st-list { display: grid; gap: 6px; }
    .st-row { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border: 1px solid var(--line); border-radius: 10px; }
    .st-row.on { border-color: var(--green); background: var(--green-soft); }
    .st-row .ttl { flex: 1; min-width: 0; }
    .st-row .ttl small { display: block; color: var(--muted); font-size: 12px; }
    .st-row .material-icons-outlined { font-size: 18px; color: var(--muted); }
    .st-row.on .material-icons-outlined { color: var(--green); }
    .go-site { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; background: var(--blue-soft); border: 1px solid var(--blue); border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; }
    .go-site p { margin: 0; color: var(--ink); }
    .go-site form { margin: 0; display: flex; gap: 8px; flex-wrap: wrap; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div><h1>Batch: {{ $batch }}</h1><div class="sub">{{ $students->count() }} students · {{ count($unlocked) }} classes unlocked</div></div>
    <a class="btn ghost" href="{{ route('admin.batches.index') }}">Back</a>
</div>

<div class="go-site">
    <p><span class="material-icons-outlined" style="color:var(--blue);vertical-align:-5px">lock_open</span>
        Lock / unlock, time and “Show answers” are done on the class pages, the same way as CD IELTS.</p>
    <form method="POST" action="{{ route('batch.choose.store') }}">
        @csrf
        <input type="hidden" name="batch" value="{{ $batch }}">
        @if (auth()->user()->canUseCourse('reading'))<button class="btn primary" name="back" value="/reading"><span class="material-icons-outlined">menu_book</span>Reading classes</button>@endif
        @if (auth()->user()->canUseCourse('writing'))<button class="btn {{ auth()->user()->canUseCourse('reading') ? '' : 'primary' }}" name="back" value="/writing"><span class="material-icons-outlined">edit_note</span>Writing classes</button>@endif
    </form>
</div>

<div class="grid" style="grid-template-columns:minmax(0,1fr) minmax(0,1fr);align-items:start">
    <div class="card">
        <h3 style="margin:0 0 12px">Students</h3>
        <div class="tbl-wrap"><table class="t">
            <tr><th>Name</th><th>Tests</th><th>Access</th></tr>
            @forelse ($students as $s)
                <tr>
                    <td><b>{{ $s->name }}</b><div class="muted" style="font-size:12px">{{ $s->email }}</div></td>
                    <td>{{ $s->results_count }}</td>
                    <td>@if ($s->access_until)<span class="badge {{ $s->accessExpired() ? 'red' : 'green' }}">{{ $s->access_until->format('d M Y') }}</span>@endif</td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">No students in this batch.</td></tr>
            @endforelse
        </table></div>
    </div>

    <div class="card">
        <h3 style="margin:0 0 12px">Class status</h3>
        @foreach ($tests as $module => $list)
            @continue(! auth()->user()->canUseCourse($module))
            <div class="muted" style="font-size:12px;font-weight:600;letter-spacing:1px;margin:10px 0 6px">{{ strtoupper($module) }}</div>
            <div class="st-list">
                @foreach ($list as $slug => $title)
                    @php($on = in_array("$module:$slug", $unlocked, true))
                    <div class="st-row {{ $on ? 'on' : '' }}">
                        <span class="material-icons-outlined">{{ $on ? 'lock_open' : 'lock' }}</span>
                        <div class="ttl">{{ $title }}<small>{{ $done["$module:$slug"] ?? 0 }} of {{ $students->count() }} submitted · {{ collect($times["$module:$slug"] ?? [])->sum('minutes') }} min{{ in_array($slug, $shown, true) && $module === 'reading' ? ' · answers shown' : '' }}</small></div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
@endsection
