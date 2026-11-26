@extends('admin.layout')
@section('title', 'Writing Submissions')

@section('content')
<div class="page-head">
    <div><h1>Writing Submissions</h1><div class="sub">{{ $results->total() }} submission(s)</div></div>
    <div class="row-acts">
        @foreach (['' => 'All', 'pending' => 'Not sent', 'sent' => 'Sent'] as $k => $label)
            <a class="btn sm {{ request('status', '') === $k ? 'primary' : 'ghost' }}" href="{{ route('admin.writing.index', array_filter(['status' => $k, 'batch' => request('batch')])) }}">{{ $label }}</a>
        @endforeach
        @if (count($batches) > 1)
            <select class="input" style="width:auto;padding:6px 12px" onchange="location.href='?status={{ request('status') }}&batch=' + this.value">
                <option value="">All batches</option>
                @foreach ($batches as $b)<option value="{{ $b }}" @selected(request('batch') === $b)>{{ $b }}</option>@endforeach
            </select>
        @endif
    </div>
</div>

<div class="card">
    <div class="tbl-wrap"><table class="t">
        <tr><th>Student</th><th>Batch</th><th>Test</th><th>Words (T1 / T2)</th><th>Status</th><th>Submitted</th><th></th></tr>
        @forelse ($results as $res)
            @php($wc = fn ($k) => str_word_count($res->answers[$k] ?? ''))
            <tr>
                <td><b>{{ $res->user->name ?? 'Deleted user' }}</b></td>
                <td>{{ $res->user->batch ?? '—' }}</td>
                <td>{{ $res->testTitle() }}</td>
                <td>{{ $wc('task1') }} / {{ $wc('task2') }}</td>
                <td>
                    @if ($res->feedback_sent_at)<span class="badge green">Band {{ $res->band }} · sent</span>
                    @elseif ($res->band !== null)<span class="badge amber">Band {{ $res->band }} · draft</span>
                    @else<span class="badge red">Not checked</span>@endif
                </td>
                <td class="muted">{{ $res->created_at->format('d M Y, h:i A') }}</td>
                <td><div class="row-acts">
                    <a class="btn sm" href="{{ route('admin.writing.show', $res) }}">{{ $res->feedback_sent_at ? 'View' : 'Check' }}</a>
                    @if (auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('admin.results.destroy', $res) }}" data-confirm="Delete this submission?">@csrf @method('DELETE')<button class="btn danger sm">Delete</button></form>
                    @endif
                </div></td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No writing submissions yet.</td></tr>
        @endforelse
    </table></div>
    <div class="pager">{{ $results->links() }}</div>
</div>
@endsection
