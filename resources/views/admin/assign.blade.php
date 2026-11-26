@extends('admin.layout')
@section('title', 'Assign Batch')

@section('content')
<div class="page-head"><div><h1>Assign Batch to Teacher</h1><div class="sub">A teacher sees only their batches: students, results, writing checking and class locks.</div></div></div>

@if ($teachers->isEmpty())
    <div class="card empty">No teachers yet. <a href="{{ route('admin.students.create', ['role' => 'teacher']) }}" style="color:var(--blue)">Register a teacher first.</a></div>
@else
<form class="card grid" method="POST" action="{{ route('admin.assign.store') }}" style="grid-template-columns:1fr 1fr auto;align-items:end">
    @csrf
    <div><label class="f">Teacher</label>
        <select name="teacher_id" class="input" required>
            @foreach ($teachers as $t)<option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>{{ $t->name }} — {{ $t->email }}</option>@endforeach
        </select>
    </div>
    <div><label class="f">Batch</label>
        @php($counts = \App\Models\User::where('role', 'student')->selectRaw('batch, COUNT(*) n')->groupBy('batch')->pluck('n', 'batch'))
        <select id="batchPick" class="input" @if(!count($batches)) style="display:none" @endif>
            <option value="" disabled @selected(!old('batch'))>— Select a batch —</option>
            @foreach ($batches as $b)<option value="{{ $b }}" @selected(old('batch') === $b)>{{ $b }} · {{ $counts[$b] ?? 0 }} students</option>@endforeach
            <option value="__new">+ New batch…</option>
        </select>
        <input name="batch" id="batchNew" class="input" placeholder="Type the new batch name" style="{{ count($batches) ? 'display:none;margin-top:6px' : '' }}" value="{{ old('batch') }}">
    </div>
    <button class="btn primary">Assign</button>
</form>

<div class="card">
    <div class="tbl-wrap"><table class="t">
        <tr><th>Teacher</th><th>Batches</th></tr>
        @foreach ($teachers as $t)
            <tr>
                <td><b>{{ $t->name }}</b><div class="muted" style="font-size:12px">{{ $t->email }}</div></td>
                <td>
                    <div style="display:flex;flex-wrap:wrap;gap:6px">
                    @forelse ($t->batches as $tb)
                        <form method="POST" action="{{ route('admin.assign.destroy', $tb) }}" data-confirm="Remove {{ $tb->batch }} from {{ $t->name }}?" style="margin:0">
                            @csrf @method('DELETE')
                            <span class="badge" style="display:inline-flex;align-items:center;gap:4px">{{ $tb->batch }}<button style="border:0;background:none;color:inherit;cursor:pointer;padding:0;line-height:1" title="Remove"><span class="material-icons-outlined" style="font-size:15px">close</span></button></span>
                        </form>
                    @empty
                        <span class="muted">No batch yet</span>
                    @endforelse
                    </div>
                </td>
            </tr>
        @endforeach
    </table></div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    var pick = document.getElementById('batchPick'), inp = document.getElementById('batchNew');
    if (!pick) return;
    function sync() {
        var isNew = pick.value === '__new' || pick.style.display === 'none';
        inp.style.display = isNew ? '' : 'none';
        inp.required = isNew;
        if (!isNew) inp.value = pick.value;
        else if (pick.value === '__new') { inp.value = ''; inp.focus(); }
    }
    pick.addEventListener('change', sync);
    pick.form.addEventListener('submit', function (e) {
        if (!inp.value.trim()) { e.preventDefault(); (pick.style.display === 'none' || pick.value === '__new' ? inp : pick).focus(); }
    });
    if (pick.value) sync();
})();
</script>
@endpush
