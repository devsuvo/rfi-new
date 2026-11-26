@extends('admin.layout')
@section('title', 'Reading Results')

@section('content')
<div class="page-head"><div><h1>Reading Result List</h1><div class="sub">{{ $results->total() }} result(s)</div></div></div>

<form class="card grid" method="GET" style="align-items:end;grid-template-columns:1fr 1fr 1.4fr">
    <div><label class="f">Batch</label>
        <select name="batch" class="input" onchange="this.form.submit()">
            <option value="">All my batches</option>
            @foreach ($batches as $b)<option value="{{ $b }}" @selected(request('batch') === $b)>{{ $b }}</option>@endforeach
        </select>
    </div>
    <div><label class="f">Test</label>
        <select name="test" class="input" onchange="this.form.submit()">
            <option value="">All tests</option>
            @foreach ($tests as $s => $t)<option value="{{ $s }}" @selected(request('test') === $s)>{{ $t }}</option>@endforeach
        </select>
    </div>
    <div><label class="f">Search student</label>
        <div style="display:flex;gap:8px"><input name="q" class="input" value="{{ request('q') }}" placeholder="Name or email"><button class="btn primary">Search</button></div>
    </div>
</form>

<div class="card">
    <div class="tbl-wrap"><table class="t">
        <tr><th>Student</th><th>Test</th><th>Score</th><th>Band</th><th>Submitted</th><th></th></tr>
        @forelse ($results as $res)
            <tr>
                <td><b>{{ $res->user->name ?? 'Deleted user' }}</b><div class="muted" style="font-size:12px">{{ $res->user->batch ?? '' }}</div></td>
                <td>{{ $res->testTitle() }}</td>
                <td>@if ($res->total){{ $res->score }} / {{ $res->total }}@else<span class="badge amber">No answer key</span>@endif</td>
                <td>@if ($res->band !== null)<span class="badge {{ $res->band >= 6.5 ? 'green' : ($res->band >= 5 ? '' : 'red') }}">{{ $res->band }}</span>@else — @endif</td>
                <td class="muted">{{ $res->created_at->format('d M Y, h:i A') }}</td>
                <td><div class="row-acts">
                    <a class="btn sm" href="{{ route('admin.reading.show', $res) }}">View</a>
                    @if (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('admin.results.destroy', $res) }}" data-confirm="Delete this result?">@csrf @method('DELETE')<button class="btn danger sm">Delete</button></form>
                    @endif
                </div></td>
            </tr>
        @empty
            <tr><td colspan="6" class="empty">No reading results yet.</td></tr>
        @endforelse
    </table></div>
    <div class="pager">{{ $results->links() }}</div>
</div>
@endsection
