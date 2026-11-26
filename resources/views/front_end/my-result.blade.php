@extends('front_end.layout.app')

@section('title', $result->testTitle())

@push('styles')
<style>
    .mr { padding: 48px 0 96px; max-width: 900px; }
    .mr .back { display: inline-flex; align-items: center; gap: 6px; color: var(--hp-navy); font-size: 13px; font-weight: 600; margin-bottom: 18px; background: #fff; padding: 7px 16px 7px 10px; border-radius: 999px; border: 1px solid var(--t-line); transition: border-color .2s, transform .2s; }
    .mr .back:hover { border-color: #0AA3B5; transform: translateY(-1px); }
    .mr .back .material-icons-outlined { font-size: 18px; }
    .mr h1 { font-size: clamp(26px, 3.6vw, 36px); color: var(--hp-navy); margin: 0 0 6px; }
    .mr .sub { color: var(--t-muted); margin: 0 0 24px; }
    .box { background: #fff; border: 1px solid var(--t-line); border-radius: 18px; padding: 22px; box-shadow: var(--shadow-sm); margin-bottom: 18px; }
    .box h3 { margin: 0 0 12px; color: var(--hp-navy); }
    .big { display: flex; gap: 24px; flex-wrap: wrap; }
    .big div { font-size: 30px; font-weight: 800; color: var(--hp-navy); }
    .big small { display: block; font-size: 12px; font-weight: 600; color: var(--t-muted); letter-spacing: 1px; }
    .qs { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 8px; }
    .q { display: flex; align-items: center; gap: 8px; border-radius: 10px; padding: 8px 10px; font-size: 14px; }
    .q.ok { background: #E7F6EC; color: #15803D; } .q.no { background: #FDECEC; color: #B91C1C; }
    .q b { min-width: 26px; }
    .q span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .essay { white-space: pre-wrap; line-height: 1.8; background: var(--t-bg); border-radius: 12px; padding: 16px; }
    .fb { white-space: pre-wrap; line-height: 1.7; background: #FFF8E6; border-left: 4px solid var(--hp-gold); border-radius: 10px; padding: 14px 16px; }
</style>
@endpush

@section('content')
<section class="mr wrap">
    <a class="back" href="{{ route('my.results') }}"><span class="material-icons-outlined">arrow_back</span>Back to My Results</a>
    <h1>{{ $result->testTitle() }}</h1>
    <p class="sub">{{ ucfirst($result->module) }} · {{ $result->created_at->format('d M Y, h:i A') }}</p>

    @if ($result->module === 'reading')
        @php
            $key = \App\Models\AnswerKey::where('module', 'reading')->where('test_slug', $result->test_slug)->value('answers') ?? [];
            // only the questions of the part handed in (class work or homework)
            $key = array_intersect_key($key, array_flip(\App\Support\Tests::readingFieldNames($result->test_slug, $result->kind === 'homework')));
            // a result of one exercise only holds that exercise's answers (p{tab}_q…): keep only those tabs
            $tabs = collect(array_keys($result->answers ?? []))->map(fn ($n) => preg_match('/^p(\d+)_/', $n, $m) ? $m[1] : null)->filter()->unique()->all();
            if ($tabs) {
                $key = array_filter($key, fn ($n) => preg_match('/^p(\d+)_/', $n, $m) && in_array($m[1], $tabs), ARRAY_FILTER_USE_KEY);
            }
            $detail = \App\Support\Grader::grade($key, $result->answers)['detail'];
            $reveal = true; // correct answers are shown right after finishing
        @endphp
        <div class="box big">
            <div>{{ $result->score }} / {{ $result->total }}<small>SCORE</small></div>
            <div>{{ $result->band }}<small>BAND</small></div>
        </div>
        @foreach (\App\Support\Tests::readingFields($result->test_slug) as $g)
            @php($rows = array_filter($g['fields'], fn ($f) => array_key_exists($f['name'], $detail)))
            @continue(! $rows)
            <div class="box">
                <h3>{{ $g['name'] }}</h3>
                <div class="qs">
                    @foreach ($rows as $f)
                        @php($v = $result->answers[$f['name']] ?? '')
                        <div class="q {{ $detail[$f['name']] ? 'ok' : 'no' }}">
                            <span class="material-icons-outlined" style="font-size:17px">{{ $detail[$f['name']] ? 'check_circle' : 'cancel' }}</span>
                            <b>{{ $f['label'] }}</b><span>{{ is_array($v) ? implode(', ', $v) : ($v !== '' ? $v : '—') }}</span>
                        </div>
                        @if ($reveal && ! $detail[$f['name']])
                            <div class="q" style="background:#F3F4F9;color:#15803D;font-size:13px"><b>{{ $f['label'] }}</b><span>✓ {{ $key[$f['name']] }}</span></div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="box big"><div>{{ $result->band }}<small>BAND</small></div></div>
        @if ($result->feedback)
            <div class="box"><h3>Teacher’s feedback</h3><div class="fb">{{ $result->feedback }}</div></div>
        @endif
        @foreach (['task1' => 'Task 1', 'task2' => 'Task 2'] as $k => $label)
            <div class="box"><h3>Your {{ $label }}</h3><div class="essay">{{ ($result->answers[$k] ?? '') ?: 'No answer.' }}</div></div>
        @endforeach
    @endif
</section>
@endsection
