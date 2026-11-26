@extends('admin.layout')
@section('title', 'Reading Result')

@push('styles')
<style>
    .ok { color: var(--green); } .bad { color: var(--red); }
    tr.wrong td { background: var(--red-soft); } tr.right td { background: var(--green-soft); }
</style>
@endpush

@section('content')
<div class="page-head">
    <div><h1>{{ $result->user->name ?? 'Deleted user' }} — {{ $result->testTitle() }}</h1>
        <div class="sub">{{ $result->created_at->format('d M Y, h:i A') }}</div></div>
    <div class="row-acts">
        @if ($result->total)<span class="badge" style="font-size:15px;padding:8px 16px">{{ $result->score }} / {{ $result->total }} · Band {{ $result->band }}</span>@endif
        <a class="btn ghost" href="{{ route('admin.reading.results') }}">Back</a>
    </div>
</div>

@unless ($key)
    <div class="errors">No answer key has been saved for this test yet.
        @if (auth()->user()->isAdmin())<a href="{{ route('admin.reading.answers', ['test' => $result->test_slug]) }}" style="text-decoration:underline">Create one</a> and the result will be marked.@else Ask the admin to add the answers.@endif
    </div>
@endunless

@foreach ($groups as $g)
    <div class="card">
        <h3 style="margin:0 0 10px">{{ $g['name'] }}</h3>
        <div class="tbl-wrap"><table class="t">
            <tr><th style="width:80px">Q</th><th>Student answer</th><th>Correct answer</th><th style="width:60px"></th></tr>
            @foreach ($g['fields'] as $f)
                @php
                    $given = $result->answers[$f['name']] ?? '';
                    $given = is_array($given) ? implode(', ', $given) : $given;
                    $state = array_key_exists($f['name'], $detail) ? ($detail[$f['name']] ? 'right' : 'wrong') : '';
                @endphp
                <tr class="{{ $state }}">
                    <td><b>{{ $f['label'] }}</b></td>
                    <td>{{ $given !== '' ? $given : '—' }}</td>
                    <td class="muted">{{ $key[$f['name']] ?? '—' }}</td>
                    <td>@if ($state === 'right')<span class="material-icons-outlined ok">check_circle</span>@elseif ($state === 'wrong')<span class="material-icons-outlined bad">cancel</span>@endif</td>
                </tr>
            @endforeach
        </table></div>
    </div>
@endforeach
@endsection
