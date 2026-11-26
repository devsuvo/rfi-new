@extends('admin.layout')
@section('title', \App\Models\User::ROLES[$role].' List')

@section('content')
<div class="page-head">
    <div><h1>{{ \App\Models\User::ROLES[$role] }} List</h1><div class="sub">{{ $users->total() }} {{ strtolower(\App\Models\User::ROLES[$role]) }}(s)</div></div>
    <a class="btn primary" href="{{ route('admin.students.create', ['role' => $role]) }}"><span class="material-icons-outlined">person_add</span>Add {{ \App\Models\User::ROLES[$role] }}</a>
</div>

<div class="row-acts" style="justify-content:flex-start;margin-bottom:14px">
    @foreach (\App\Models\User::ROLES as $k => $label)
        <a class="btn sm {{ $role === $k ? 'primary' : 'ghost' }}" href="{{ route('admin.students.index', ['role' => $k]) }}">{{ $label }}s · {{ $counts[$k] ?? 0 }}</a>
    @endforeach
</div>

<form class="card" method="GET" style="display:flex;gap:8px;flex-wrap:wrap">
    <input type="hidden" name="role" value="{{ $role }}">
    <input name="q" class="input" style="flex:1;min-width:200px" value="{{ request('q') }}" placeholder="Search by name, email or phone">
    @if ($role !== 'admin')
        <select name="course" class="input" style="width:auto" onchange="this.form.submit()">
            <option value="">All courses</option>
            @foreach (\App\Models\User::COURSES as $k => $label)<option value="{{ $k }}" @selected(request('course') === $k)>{{ $label }}</option>@endforeach
        </select>
    @endif
    @if ($role === 'student')
        <select name="batch" class="input" style="width:auto" onchange="this.form.submit()">
            <option value="">All batches</option>
            @foreach ($batches as $b)<option value="{{ $b }}" @selected(request('batch') === $b)>{{ $b }}</option>@endforeach
        </select>
    @endif
    <button class="btn primary">Search</button>
</form>

<div class="card">
    @if ($users->count())<div style="display:flex;justify-content:flex-end;gap:8px;margin-bottom:10px">@if ($role === 'student')@if (request('batch'))<a class="btn sm" href="{{ route('admin.students.logins.pdf', request()->only('batch', 'course')) }}">Download PDF</a>@else<span class="hint" style="align-self:center">Pick a batch to download its PDF</span>@endif @endif<button type="button" class="btn sm primary" id="copyAll" data-url="{{ route('admin.students.logins', request()->only('role', 'q', 'batch', 'course')) }}">Copy all {{ $users->total() }} logins</button></div>@endif
    <div class="tbl-wrap"><table class="t">
        <tr>
            <th>#</th><th>Name</th>@if ($role !== 'admin')<th>Course</th>@endif<th>Email</th><th>Phone</th>
            @if ($role === 'student')<th>Batch</th><th>Access until</th><th>Tests</th>@endif
            @if ($role === 'teacher')<th>Batches</th>@endif
            <th></th>
        </tr>
        @forelse ($users as $u)
            @php($local = strstr($u->email, '@', true))
            @php($pass = $u->auto_password ? $local : null)
            <tr class="u-row" data-cred="Email: {{ $u->email }}{{ $pass ? "
Password: ".$pass : '' }}">
                <td class="muted">{{ $users->firstItem() + $loop->index }}</td>
                <td><b>{{ $u->name }}</b></td>
                @if ($role !== 'admin')<td><span class="badge {{ $u->courseKey() === 'writing' ? 'red' : '' }}">{{ ucfirst($u->courseKey()) }}</span></td>@endif
                <td>{{ $u->email }}</td>
                <td>{{ $u->phone ?: '—' }}</td>
                @if ($role === 'student')
                    <td>@if ($u->batch)<a class="badge" href="{{ route('admin.batches.show', $u->batch) }}">{{ $u->batch }}</a>@else — @endif</td>
                    <td>@if ($u->access_until)<span class="badge {{ $u->accessExpired() ? 'red' : 'green' }}">{{ $u->access_until->format('d M Y') }}</span>@else — @endif</td>
                    <td>{{ $u->results_count }}</td>
                @endif
                @if ($role === 'teacher')
                    <td>@forelse ($u->batches as $tb)<span class="badge" style="margin:2px">{{ $tb->batch }}</span>@empty<span class="muted">No batch yet</span>@endforelse</td>
                @endif
                <td><div class="row-acts">
                    <button type="button" class="btn sm ghost copy-cred" title="{{ $pass ? 'Copy email + password' : 'Copy email (password was set by hand, so it is not known)' }}">{{ $pass ? 'Copy login' : 'Copy email' }}</button>
                    <a class="btn sm" href="{{ route('admin.students.edit', $u) }}">Edit</a>
                    @unless ($u->is(auth()->user()))
                        <form method="POST" action="{{ route('admin.students.destroy', $u) }}" data-confirm="Delete {{ $u->name }}?">@csrf @method('DELETE')<button class="btn danger sm">Delete</button></form>
                    @endunless
                </div></td>
            </tr>
        @empty
            <tr><td colspan="9" class="empty">Nothing found.</td></tr>
        @endforelse
    </table></div>
    <div class="pager">{{ $users->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    function copy(text, btn) {
        var done = function () { var t = btn.textContent; btn.textContent = 'Copied ✓'; setTimeout(function () { btn.textContent = t; }, 1500); };
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(text).then(done, function () { fallback(text); done(); });
        fallback(text); done();
    }
    function fallback(text) {
        var ta = document.createElement('textarea'); ta.value = text; document.body.appendChild(ta); ta.style.position = 'fixed'; ta.style.opacity = '0'; ta.select(); document.execCommand('copy'); ta.remove();
    }
    document.querySelectorAll('.copy-cred').forEach(function (b) {
        b.onclick = function () { copy(b.closest('tr').dataset.cred, b); };
    });
    var all = document.getElementById('copyAll');
    if (all) all.onclick = function () {
        var label = all.textContent; all.textContent = 'Loading…'; all.disabled = true;
        fetch(all.dataset.url, { credentials: 'same-origin' }).then(function (r) { return r.text(); }).then(function (text) {
            all.disabled = false; all.textContent = label; copy(text, all);
        }).catch(function () { all.disabled = false; all.textContent = 'Failed — try again'; });
    };
})();
</script>
@endpush
