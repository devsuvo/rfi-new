<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ ! empty($isHomework) ? 'Homework · ' : '' }}{{ $class['title'] }} · Reading for IELTS</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
</head>
<body>
@include('front_end.layout.test-theme', ['tone' => '#0aa3b5', 'soft' => '#e3f6f8', 'module' => 'Reading', 'noTimer' => empty($isHomework) || ! empty($practice),'minutes' => $totalMinutes ?? (array_sum(array_column($panes, 'minutes')) ?: 60), 'submit' => ! empty($isHomework) ? route('results.homework', $class['slug']) : route('results.store', ['reading', $class['slug']]), 'backUrl' => route('reading'), 'liveUrl' => route('tests.live', ['reading', $class['slug']])])

<div class="t-head"><span class="dot"></span><strong>{{ ! empty($isHomework) ? 'Homework' : 'Reading for IELTS' }}</strong><span>{{ $class['title'] }}</span>
    @if (empty($isHomework) && ! empty($homeworkCount) && auth()->user()->isStaff())
        <a class="t-hw" style="text-decoration:none" href="{{ route('reading.homework', $class['slug']) }}"><span class="material-icons-outlined">visibility</span>Preview homework ({{ $homeworkCount }})</a>
    @endif
    @if (! empty($practice))
        <span class="t-hw" style="background:#ECFDF3;color:#15803D;border-color:#A7E3BF" title="Your first attempt was sent to your teacher. Practice tries are marked here but not sent."><span class="material-icons-outlined">replay</span>Practice · not sent to your teacher</span>
    @elseif (! empty($homework))
        <span class="t-hw {{ $homework->overdue() ? 'late' : '' }}" title="{{ $homework->note }}"><span class="material-icons-outlined">home_work</span>Homework · {{ $homework->overdue() ? 'Overdue · ' : '' }}{{ $homework->dueLabel() }}</span>
    @endif
</div>
@if (! empty($homework) && $homework->note)
    <div class="t-hw-note"><span class="material-icons-outlined">sticky_note_2</span>{{ $homework->note }}</div>
@endif

<div class="t-tabs">
    @foreach ($panes as $pane)
        <section class="t-pane {{ $pane['single'] ? 'single' : '' }}" data-tab="{{ $pane['name'] }}" data-part="{{ $pane['part'] ?? '' }}" @if (! empty($pane['minutes'])) data-minutes="{{ $pane['minutes'] }}" @endif>
            <div class="t-passage">{!! $pane['passage'] !!}</div>
            @unless ($pane['single'])
                <div class="t-questions">{!! $pane['questions'] !!}</div>
            @endunless
        </section>
    @endforeach
</div>

@include('front_end.layout.letter-picker')
</body>
</html>
