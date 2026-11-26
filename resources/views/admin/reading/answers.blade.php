@extends('admin.layout')
@section('title', 'Reading Answers')

@push('styles')
<style>
    .syntax { background: var(--cyan-soft); color: #0A8FA0; border-radius: 10px; padding: 11px 16px; font-size: 13px; margin: 18px 0 16px; line-height: 1.7; }
    .syntax em { font-style: normal; color: #DB2777; }
    .grp { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; margin-top: 16px; }
    .grp-head { display: flex; justify-content: space-between; align-items: center; background: var(--head); padding: 11px 14px; }
    .grp-head h4 { margin: 0; font-size: 15px; color: var(--blue); font-weight: 600; }
    .grp-head .badge { background: var(--panel); }
    .rows { padding: 12px; display: grid; gap: 8px; }
    .arow { display: flex; align-items: stretch; }
    .arow .num { width: 50px; flex: none; display: grid; place-items: center; border: 1px solid var(--line); border-right: 0; border-radius: 8px 0 0 8px; background: var(--panel); font-weight: 500; }
    .arow input { flex: 1; min-width: 0; border: 1px solid var(--line); background: var(--panel); color: var(--ink); padding: 9px 14px; font: inherit; }
    .arow input:focus { outline: none; border-color: var(--blue); }
    .arow .x { width: 52px; flex: none; border: 0; background: var(--red); color: #fff; border-radius: 0 999px 999px 0; cursor: pointer; display: grid; place-items: center; }
    .arow .x .material-icons-outlined { font-size: 16px; }
    .arow.multi .num { width: 70px; font-size: 13px; }
    .bar-acts { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
    .main-grid { display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 20px; align-items: start; }
    .side-card { position: sticky; top: 20px; }
    .side-card ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 4px; max-height: 60vh; overflow: auto; }
    .side-card li a { display: flex; justify-content: space-between; gap: 8px; padding: 8px 10px; border-radius: 8px; font-size: 13px; }
    .side-card li a:hover { background: var(--panel-2); }
    .side-card li a.on { background: var(--blue-soft); color: var(--blue); font-weight: 600; }
    .chk { display: flex; gap: 8px; align-items: center; color: var(--muted); font-size: 13px; }
    @media (max-width: 1100px) { .main-grid { grid-template-columns: 1fr; } .side-card { position: static; } }
</style>
@endpush

@section('content')
@php
    $answers = old('answers', $key->answers ?? []);
    $known = collect($groups)->flatMap(fn ($g) => array_column($g['fields'], 'name'))->all();
    $extra = array_diff(array_keys($answers), $known);
@endphp

<div class="card">
    <label class="f" for="pick">Edit saved answers</label>
    <select id="pick" class="input" onchange="location.href='{{ route('admin.reading.answers') }}?test=' + this.value">
        @foreach ($tests as $s => $title)
            <option value="{{ $s }}" @selected($s === $slug)>{{ $title }}{{ in_array($s, $saved) ? '  ✓ saved' : '' }}</option>
        @endforeach
    </select>
</div>

<form method="POST" action="{{ route('admin.reading.answers.save') }}" id="akForm">
    @csrf
    <input type="hidden" name="test_slug" value="{{ $slug }}">
    <div class="page-head">
        <h1>{{ $key->exists ? 'Edit' : 'Create' }} Reading Answers — {{ $tests[$slug] }}</h1>
        <div class="bar-acts">
            <a href="{{ route('admin.reading.answers', ['test' => $slug]) }}" class="btn ghost">Discard</a>
            <button class="btn primary"><span class="material-icons-outlined">save</span>Save All Answers</button>
        </div>
    </div>

    <div class="main-grid">
        <div class="card">
            <label class="f" for="tn">Test Name (ex: class18_reading)</label>
            <input id="tn" class="input" name="test_name" value="{{ old('test_name', $key->test_name) }}" required>
            <div class="hint">Use the same name as on the test page.</div>

            <div class="syntax">
                Alternative answer: <em>£50 deposit / deposit of £50</em> (space-slash-space) ·
                Word option: <em>section/area</em> ·
                Optional part: <em>Park View (Hotel)</em>, <em>4(th) November</em> ·
                Choose TWO: <em>B,D</em> (any order) · Case and extra spaces are ignored.
            </div>

            <div class="bar-acts">
                <a href="{{ route('admin.reading.answers', ['test' => $slug, 'build' => 1]) }}" class="btn ghost sm">Create a box for every question</a>
                <a href="{{ route('reading.class', $slug) }}" target="_blank" class="btn ghost sm"><span class="material-icons-outlined" style="font-size:16px">open_in_new</span>Open test page</a>
                <span class="muted" id="cnt"></span>
            </div>

            @forelse ($groups as $g)
                @php($rows = array_filter($g['fields'], fn ($f) => $build || isset($answers[$f['name']])))
                @continue(! $rows)
                @php($nums = collect($rows)->flatMap(fn ($f) => explode(' & ', $f['label'])))
                <div class="grp">
                    <div class="grp-head"><h4>{{ $g['name'] }}</h4><span class="badge">Questions {{ $nums->first() }}–{{ $nums->last() }}</span></div>
                    <div class="rows">
                        @foreach ($rows as $f)
                            <div class="arow {{ $f['multi'] ? 'multi' : '' }}">
                                <span class="num">{{ $f['label'] }}</span>
                                <input name="answers[{{ $f['name'] }}]" value="{{ $answers[$f['name']] ?? '' }}" placeholder="{{ $f['multi'] ? 'e.g. B,D' : 'Answer' }}" autocomplete="off">
                                <button type="button" class="x" title="Remove"><span class="material-icons-outlined">close</span></button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="empty">No answer boxes found on this test page.</div>
            @endforelse

            @if ($extra)
                <div class="grp">
                    <div class="grp-head"><h4>Not on the page any more</h4><span class="badge">{{ count($extra) }}</span></div>
                    <div class="rows">
                        @foreach ($extra as $name)
                            <div class="arow"><span class="num" style="font-size:11px">{{ $name }}</span><input name="answers[{{ $name }}]" value="{{ $answers[$name] }}"><button type="button" class="x"><span class="material-icons-outlined">close</span></button></div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (! $build && $key->exists && count($answers) < count($known))
                <p class="hint">{{ count($known) - count($answers) }} question(s) have no answer yet — click “Create a box for every question” to fill them in.</p>
            @endif

            <label class="chk" style="margin-top:18px"><input type="checkbox" name="regrade" value="1" checked> Re-mark this test’s old results with these answers</label>
        </div>

        <div class="card side-card">
            <h4 style="margin:0 0 10px">All reading tests</h4>
            <ul>
                @foreach ($tests as $s => $title)
                    <li><a href="{{ route('admin.reading.answers', ['test' => $s]) }}" class="{{ $s === $slug ? 'on' : '' }}"><span>{{ $title }}</span>@if (in_array($s, $saved))<span class="badge green">✓</span>@endif</a></li>
                @endforeach
            </ul>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('akForm'), cnt = document.getElementById('cnt');
    function count() {
        var all = form.querySelectorAll('.arow input'), filled = [].filter.call(all, function (i) { return i.value.trim(); }).length;
        cnt.textContent = filled + ' / ' + all.length + ' filled';
    }
    form.addEventListener('click', function (e) {
        var x = e.target.closest('.x'); if (!x) return;
        var row = x.closest('.arow'), grp = row.closest('.grp');
        row.remove();
        if (!grp.querySelector('.arow')) grp.remove();
        count();
    });
    form.addEventListener('input', count);
    // Enter jumps to the next box instead of submitting.
    form.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || !e.target.closest('.arow')) return;
        e.preventDefault();
        var all = [].slice.call(form.querySelectorAll('.arow input')), i = all.indexOf(e.target);
        if (all[i + 1]) all[i + 1].focus();
    });
    count();
})();
</script>
@endpush
