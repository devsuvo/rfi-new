@extends('admin.layout')
@section('title', 'Check Writing')

@push('styles')
<style>
    .essay { white-space: pre-wrap; line-height: 1.8; background: var(--panel-2); border: 1px solid var(--line); border-radius: 12px; padding: 16px; min-height: 120px; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div><h1>{{ $result->user->name ?? 'Deleted user' }} — {{ $result->testTitle() }}</h1>
        <div class="sub">Batch {{ $result->user->batch ?? '—' }} · submitted {{ $result->created_at->format('d M Y, h:i A') }}</div></div>
    <div class="row-acts">
        @if ($result->feedback_sent_at)
            <span class="badge green">Sent {{ $result->feedback_sent_at->format('d M, h:i A') }}</span>
        @elseif ($result->band !== null)
            <span class="badge amber">Draft — not sent yet</span>
        @endif
        <a class="btn ghost" href="{{ route('admin.writing.index') }}">Back</a>
    </div>
</div>

@foreach (['task1' => 'Task 1', 'task2' => 'Task 2'] as $k => $label)
    @php($text = $result->answers[$k] ?? '')
    <div class="card">
        <div style="display:flex;justify-content:space-between;margin-bottom:10px"><h3 style="margin:0">{{ $label }}</h3><span class="badge">{{ str_word_count($text) }} words</span></div>
        <div class="essay">{{ $text !== '' ? $text : 'No answer.' }}</div>
    </div>
@endforeach

<form class="card" method="POST" action="{{ route('admin.writing.mark', $result) }}">
    @csrf
    <h3 style="margin:0 0 14px">Band &amp; feedback</h3>
    <div class="grid" style="grid-template-columns:160px 1fr">
        <div><label class="f">Band (0–9)</label>
            <select name="band" class="input" required>
                <option value="" hidden>Choose</option>
                @foreach (range(0, 9, 0.5) as $b)<option value="{{ $b }}" @selected(old('band', $result->band) !== null && (float) old('band', $result->band) == $b)>{{ number_format($b, 1) }}</option>@endforeach
            </select>
        </div>
        <div><label class="f">Feedback for the student</label><textarea name="feedback" class="input" rows="6" placeholder="What went well, what to improve…">{{ old('feedback', $result->feedback) }}</textarea></div>
    </div>
    <div class="row-acts" style="justify-content:flex-start;margin-top:14px">
        <button class="btn" name="send" value="0">Save draft</button>
        <button class="btn primary" name="send" value="1"><span class="material-icons-outlined">send</span>{{ $result->feedback_sent_at ? 'Send again' : 'Send to student' }}</button>
    </div>
    <p class="hint">The student sees the band and feedback only after you press “Send to student”.</p>
</form>
@endsection
