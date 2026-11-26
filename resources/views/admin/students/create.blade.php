@extends('admin.layout')
@section('title', 'Add User')

@section('content')
<div class="page-head"><div><h1>Register Users</h1><div class="sub">Add one or many at once — only name and email are needed. Passwords are created automatically.</div></div></div>

@if ($created = session('created'))
<div class="card" style="max-width:900px">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:10px">
        <b>Login details — copy them now, the passwords are shown only once.</b>
        <button type="button" class="btn sm primary" id="copyCreds">Copy all</button>
    </div>
    <div class="tbl-wrap"><table class="t">
        <tr><th>Name</th><th>Email (login)</th><th>Password</th></tr>
        @foreach ($created as $c)
            <tr><td>{{ $c['name'] }}</td><td>{{ $c['email'] }}</td><td><code>{{ $c['password'] }}</code></td></tr>
        @endforeach
    </table></div>
    <textarea id="credsText" hidden>@foreach ($created as $c){{ $c['name'] }}	{{ $c['email'] }}	{{ $c['password'] }}
@endforeach</textarea>
</div>
@endif

<form class="card" method="POST" action="{{ route('admin.students.store') }}" style="max-width:900px" id="bulkForm">
    @csrf
    <div class="grid g2">
        <div><label class="f">Role *</label>
            <select name="role" id="role" class="input">
                @foreach (\App\Models\User::ROLES as $k => $label)<option value="{{ $k }}" @selected(old('role', $role) === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div class="not-admin"><label class="f">Course *</label>
            <select name="course" class="input">
                @foreach (\App\Models\User::COURSES as $k => $label)<option value="{{ $k }}" @selected(old('course', 'reading') === $k)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div class="only-student"><label class="f">Batch *</label>
            <input name="batch" class="input" list="batchList" value="{{ old('batch') }}" placeholder="Pick or type a new batch name">
            <datalist id="batchList">@foreach ($batches as $b)<option value="{{ $b }}">@endforeach</datalist>
        </div>
        <div class="only-student"><label class="f">Access until</label>
            <input name="access_until" type="date" class="input" value="{{ old('access_until') }}">
            <div class="hint">Empty = {{ \App\Models\User::STUDENT_ACCESS_MONTHS }} months from today.</div>
        </div>
    </div>

    <details style="margin-top:18px" @if(!old('people')) open @endif>
        <summary style="cursor:pointer;font-weight:600">Paste a list (fastest)</summary>
        <div class="hint" style="margin:6px 0">One name per line — that's enough. Email and password are made from the first name + batch number (e.g. <code>rahim.hz12@hz.com</code> / <code>rahim.hz12</code>). You can also add <code>, phone</code> or <code>, email</code> after a name.</div>
        <textarea id="pasteBox" class="input" rows="5" placeholder="Rahim Uddin&#10;Karima Akter&#10;Sabbir Hossain, 01700000000"></textarea>
        <button type="button" class="btn sm" id="addPasted" style="margin-top:8px">Add to list ↓</button>
    </details>

    <div style="margin-top:18px">
        <label class="f">People <span class="muted" id="countLbl"></span></label>
        <div class="tbl-wrap"><table class="t" id="rows">
            <tr><th>#</th><th>Full name *</th><th>Email (login)</th><th>Phone</th><th>Password</th><th></th></tr>
        </table></div>
        <button type="button" class="btn sm ghost" id="addRow" style="margin-top:8px">+ Add row</button>
        <div class="hint">Students: leave email/password empty — they become <b>firstname + batch number</b> (<code>…@hz.com</code>). Teachers/admins need an email. Empty rows are ignored.</div>
    </div>

    <div class="row-acts" style="justify-content:flex-start;margin-top:18px">
        <button class="btn primary" id="submitBtn">Register</button>
        <a class="btn ghost" href="{{ route('admin.students.index', ['role' => $role]) }}">Cancel</a>
    </div>
    <p class="hint" id="roleHint"></p>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var role = document.getElementById('role'), hint = document.getElementById('roleHint');
    var table = document.getElementById('rows'), n = 0;
    var hints = {
        student: 'Students only see their own course, and only the classes their teacher has unlocked for their batch.',
        teacher: 'Teachers only see their own course. After saving, go to “Assign Batch” to give this teacher their batches.',
        admin: 'Admins can do everything.'
    };
    function sync() {
        document.querySelectorAll('.only-student').forEach(function (el) { el.style.display = role.value === 'student' ? '' : 'none'; });
        document.querySelectorAll('.not-admin').forEach(function (el) { el.style.display = role.value === 'admin' ? 'none' : ''; });
        hint.textContent = hints[role.value] || '';
    }
    function esc(v) { return String(v || '').replace(/[&"<>]/g, function (c) { return {'&':'&amp;','"':'&quot;','<':'&lt;','>':'&gt;'}[c]; }); }
    function renumber() {
        var filled = 0;
        table.querySelectorAll('tr.p').forEach(function (tr, i) {
            tr.querySelector('.num').textContent = i + 1;
            if (tr.querySelector('[data-k=name]').value.trim() || tr.querySelector('[data-k=email]').value.trim()) filled++;
        });
        previews();
        document.getElementById('countLbl').textContent = filled ? '· ' + filled + ' to register' : '';
        document.getElementById('submitBtn').textContent = filled > 1 ? 'Register ' + filled + ' people' : 'Register';
    }
    function addRow(p) {
        p = p || {};
        var i = n++, tr = document.createElement('tr');
        tr.className = 'p';
        tr.innerHTML = '<td class="num muted"></td>' +
            '<td><input class="input" data-k="name" name="people[' + i + '][name]" value="' + esc(p.name) + '"></td>' +
            '<td><input class="input" data-k="email" type="email" name="people[' + i + '][email]" value="' + esc(p.email) + '"></td>' +
            '<td><input class="input" name="people[' + i + '][phone]" value="' + esc(p.phone) + '"></td>' +
            '<td><input class="input" data-k="pass" name="people[' + i + '][password]" value="' + esc(p.password) + '"></td>' +
            '<td><button type="button" class="btn sm danger" title="Remove">✕</button></td>';
        tr.querySelector('button').onclick = function () { tr.remove(); if (!table.querySelector('tr.p')) addRow(); renumber(); };
        tr.querySelectorAll('input').forEach(function (inp) { inp.addEventListener('input', renumber); });
        // Enter in the last row adds a new row instead of submitting.
        tr.querySelectorAll('input').forEach(function (inp) {
            inp.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); if (tr === lastRow()) addRow(); lastRow().querySelector('input').focus(); }
            });
        });
        table.appendChild(tr);
        renumber();
        return tr;
    }
    var batchInp = document.querySelector('[name=batch]');
    function previews() {
        var b = batchInp.value, digits = (b.match(/\d+/g) || []).join(''), tag = digits || b.toLowerCase().replace(/[^a-z0-9]/g, ''), seen = {};
        table.querySelectorAll('tr.p').forEach(function (tr) {
            var name = tr.querySelector('[data-k=name]').value.trim(), e = tr.querySelector('[data-k=email]'), pw = tr.querySelector('[data-k=pass]');
            if (role.value !== 'student' || !name) { e.placeholder = role.value === 'student' ? 'auto' : 'required'; pw.placeholder = 'auto'; return; }
            var first = name.split(/\s+/)[0].toLowerCase().replace(/[^a-z0-9]/g, '') || 'student', login = first + '.hz' + tag, k = 2;
            while (seen[login]) login = first + (k++) + '.hz' + tag;
            seen[login] = 1;
            e.placeholder = login + '@hz.com';
            pw.placeholder = (login + '111111').slice(0, Math.max(6, login.length));
        });
    }
    batchInp.addEventListener('input', previews);
    function lastRow() { var r = table.querySelectorAll('tr.p'); return r[r.length - 1]; }
    function removeEmpty() { table.querySelectorAll('tr.p').forEach(function (tr) { if (!tr.querySelector('[data-k=name]').value.trim() && !tr.querySelector('[data-k=email]').value.trim()) tr.remove(); }); }

    document.getElementById('addRow').onclick = function () { addRow().querySelector('input').focus(); };
    document.getElementById('addPasted').onclick = function () {
        var box = document.getElementById('pasteBox'), added = 0;
        removeEmpty();
        box.value.split(/\r?\n/).forEach(function (line) {
            if (!line.trim()) return;
            var parts = line.split(/\t|,|;/).map(function (s) { return s.trim(); });
            var email = parts.find(function (s) { return /@/.test(s); }) || '';
            var rest = parts.filter(function (s) { return s && s !== email; });
            var phone = rest.find(function (s) { return /^\+?[\d\s-]{6,}$/.test(s); }) || '';
            var name = rest.filter(function (s) { return s !== phone; }).join(' ');
            addRow({ name: name, email: email, phone: phone }); added++;
        });
        if (!table.querySelector('tr.p')) addRow();
        box.value = '';
    };

    var old = @json(array_values(old('people', [])));
    if (old.length) old.forEach(addRow); else { addRow(); addRow(); addRow(); }

    var copy = document.getElementById('copyCreds');
    if (copy) copy.onclick = function () {
        navigator.clipboard.writeText(document.getElementById('credsText').value).then(function () { copy.textContent = 'Copied ✓'; });
    };
    role.addEventListener('change', function () { sync(); previews(); }); sync();
})();
</script>
@endpush
