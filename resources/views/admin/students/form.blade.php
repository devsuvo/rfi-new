@extends('admin.layout')
@section('title', $user->exists ? 'Edit User' : 'Add User')

@section('content')
<div class="page-head"><h1>{{ $user->exists ? 'Edit '.$user->name : 'Register New User' }}</h1></div>

<form class="card" method="POST" action="{{ $user->exists ? route('admin.students.update', $user) : route('admin.students.store') }}" style="max-width:760px">
    @csrf @if ($user->exists) @method('PUT') @endif
    @php($self = $user->is(auth()->user()))
    <div class="grid g2">
        <div><label class="f">Role *</label>
            <select name="role" id="role" class="input" @disabled($self)>
                @foreach (\App\Models\User::ROLES as $k => $label)<option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $label }}</option>@endforeach
            </select>
            @if ($self)<input type="hidden" name="role" value="admin">@endif
        </div>
        <div class="not-admin"><label class="f">Course *</label>
            <select name="course" class="input">
                @foreach (\App\Models\User::COURSES as $k => $label)<option value="{{ $k }}" @selected(old('course', $user->course ?? 'reading') === $k)>{{ $label }}</option>@endforeach
            </select>
            <div class="hint">They only see this course on the site.</div>
        </div>
        <div><label class="f">Full name *</label><input name="name" class="input" value="{{ old('name', $user->name) }}" required></div>
        <div><label class="f">Email (login) *</label><input name="email" type="email" class="input" value="{{ old('email', $user->email) }}" required></div>
        <div><label class="f">Phone</label><input name="phone" class="input" value="{{ old('phone', $user->phone) }}"></div>
        <div><label class="f">Password {{ $user->exists ? '(leave empty to keep)' : '*' }}</label><input name="password" type="text" class="input" autocomplete="new-password" {{ $user->exists ? '' : 'required' }} minlength="6"></div>
        <div class="only-student"><label class="f">Batch *</label>
            <input name="batch" class="input" list="batchList" value="{{ old('batch', $user->batch) }}" placeholder="e.g. batch-12 (new name creates a new batch)">
            <datalist id="batchList">@foreach ($batches as $b)<option value="{{ $b }}">@endforeach</datalist>
        </div>
        <div class="only-student"><label class="f">Access until</label>
            <input name="access_until" type="date" class="input" value="{{ old('access_until', $user->access_until?->format('Y-m-d')) }}">
            <div class="hint">Empty = {{ \App\Models\User::STUDENT_ACCESS_MONTHS }} months from registration. The student cannot log in after this date.</div>
        </div>
    </div>
    <div class="row-acts" style="justify-content:flex-start;margin-top:18px">
        <button class="btn primary">{{ $user->exists ? 'Save changes' : 'Register' }}</button>
        <a class="btn ghost" href="{{ route('admin.students.index', ['role' => $user->role ?: 'student']) }}">Cancel</a>
    </div>
    <p class="hint" id="roleHint"></p>
</form>
@endsection

@push('scripts')
<script>
(function () {
    var role = document.getElementById('role'), hint = document.getElementById('roleHint');
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
    role.addEventListener('change', sync); sync();
})();
</script>
@endpush
