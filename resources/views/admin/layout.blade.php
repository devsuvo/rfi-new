<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ ucfirst(session('panel', 'reading')) }} for IELTS Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script>try { if (localStorage.getItem('admin-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}</script>
    <style>
        :root {
            --bg: #F4F6FB; --panel: #FFFFFF; --panel-2: #F7F9FD; --ink: #111A33; --muted: #6B7590; --line: #E4E8F1;
            --blue: #2563EB; --blue-soft: #E8EFFE; --head: #EAF1FE; --red: #EF4444; --red-soft: #FDECEC;
            --green: #16A34A; --green-soft: #E7F6EC; --amber: #D97706; --amber-soft: #FEF3E2; --cyan-soft: #DDF4F7;
            --shadow: 0 1px 2px rgba(17,26,51,.04), 0 6px 20px rgba(17,26,51,.05);
        }
        :root[data-theme="dark"] {
            --bg: #0D1324; --panel: #151C31; --panel-2: #1A2239; --ink: #E7ECF7; --muted: #94A0BC; --line: #26304A;
            --blue: #5B8CFF; --blue-soft: #1D2A4D; --head: #1B2643; --red: #F26B6B; --red-soft: #3A1D25;
            --green: #3CC873; --green-soft: #15301F; --amber: #F2A93B; --amber-soft: #36290F; --cyan-soft: #12323A;
            --shadow: 0 1px 2px rgba(0,0,0,.3);
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body { font-family: 'Poppins', 'Hind Siliguri', system-ui, sans-serif; background: var(--bg); color: var(--ink); font-size: 14px; -webkit-font-smoothing: antialiased; }
        a { color: inherit; text-decoration: none; }
        .material-icons-outlined { font-size: 20px; vertical-align: middle; }

        /* Sidebar */
        .side { position: fixed; inset: 0 auto 0 0; width: 260px; background: var(--panel); border-right: 1px solid var(--line); display: flex; flex-direction: column; padding: 18px 12px; z-index: 40; transition: transform .25s; }
        .brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 22px; font-weight: 700; font-size: 20px; }
        .brand .logo { width: 36px; height: 36px; border-radius: 10px; background: var(--blue); color: #fff; display: grid; place-items: center; }
        .brand b { color: var(--blue); }
        .menu-label { font-size: 11px; font-weight: 600; letter-spacing: 1.5px; color: var(--muted); padding: 6px 12px 8px; }
        nav.menu { flex: 1; min-height: 0; overflow-y: auto; overflow-x: hidden; display: flex; flex-direction: column; gap: 4px; padding-right: 4px; margin-right: -4px; scrollbar-width: thin; scrollbar-color: rgba(127,140,170,.35) transparent; overscroll-behavior: contain; }
        nav.menu::-webkit-scrollbar { width: 5px; }
        nav.menu::-webkit-scrollbar-track { background: transparent; }
        nav.menu::-webkit-scrollbar-thumb { background: rgba(127,140,170,.3); border-radius: 10px; }
        nav.menu:hover::-webkit-scrollbar-thumb { background: rgba(127,140,170,.55); }
        nav.menu::-webkit-scrollbar-button { display: none; height: 0; }
        .m-item, .m-sub a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-weight: 500; color: var(--ink); cursor: pointer; border: 0; background: none; width: 100%; font: inherit; text-align: left; }
        .m-item:hover, .m-sub a:hover { background: var(--panel-2); }
        .m-item .chev { margin-left: auto; transition: transform .2s; }
        details[open] > .m-item { background: var(--blue-soft); color: var(--blue); }
        details[open] > .m-item .chev { transform: rotate(90deg); }
        details > summary { list-style: none; } details > summary::-webkit-details-marker { display: none; }
        .m-sub { display: flex; flex-direction: column; gap: 4px; padding: 6px 0; }
        .m-sub a { padding-left: 42px; position: relative; }
        .m-sub a::before { content: ''; position: absolute; left: 22px; width: 5px; height: 5px; border-radius: 50%; border: 1.5px solid var(--muted); }
        .m-sub a.on, a.m-item.on { background: var(--blue); color: #fff; box-shadow: 0 6px 16px -6px var(--blue); }
        .m-sub a.on::before { border-color: #fff; }
        .me { border: 1px solid var(--line); border-radius: 14px; padding: 12px; background: var(--panel-2); display: grid; gap: 10px; }
        .me-row { display: flex; align-items: center; gap: 10px; }
        .avatar { width: 38px; height: 38px; border-radius: 50%; background: var(--blue); color: #fff; display: grid; place-items: center; font-weight: 700; position: relative; flex: none; }
        .avatar::after { content: ''; position: absolute; right: 0; bottom: 0; width: 9px; height: 9px; border-radius: 50%; background: var(--green); border: 2px solid var(--panel); }
        .me small { display: block; color: var(--muted); font-size: 12px; }
        .theme-tg { display: flex; align-items: center; justify-content: space-between; border: 1px solid var(--line); border-radius: 10px; padding: 7px 10px; background: var(--panel); cursor: pointer; color: var(--muted); font: inherit; width: 100%; }
        .switch { width: 40px; height: 22px; border-radius: 999px; background: var(--line); position: relative; }
        .switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: transform .2s; }
        [data-theme="dark"] .switch { background: var(--blue); } [data-theme="dark"] .switch::after { transform: translateX(18px); }
        .logout { display: flex; align-items: center; justify-content: center; gap: 6px; border: 1px solid var(--red); color: var(--red); background: var(--red-soft); border-radius: 10px; padding: 9px; font-weight: 500; }

        /* Main */
        main { margin-left: 260px; padding: 22px 26px 60px; min-height: 100vh; }
        .topbar { display: none; }
        .page-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin: 6px 0 20px; }
        .page-head h1 { font-size: 24px; font-weight: 600; margin: 0; }
        .page-head .sub { color: var(--muted); margin-top: 4px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 18px; padding: 22px; box-shadow: var(--shadow); margin-bottom: 20px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; border: 0; border-radius: 999px; padding: 10px 20px; font: inherit; font-weight: 600; cursor: pointer; background: var(--blue-soft); color: var(--blue); }
        .btn.primary { background: var(--blue); color: #fff; }
        .btn.ghost { background: var(--panel-2); color: var(--muted); border: 1px solid var(--line); }
        .btn.danger { background: var(--red-soft); color: var(--red); }
        .btn.sm { padding: 6px 12px; font-size: 13px; }
        label.f { display: block; color: var(--muted); margin-bottom: 6px; }
        .input, select.input, textarea.input { width: 100%; border: 1px solid var(--line); background: var(--panel); color: var(--ink); border-radius: 10px; padding: 10px 14px; font: inherit; }
        .input:focus { outline: none; border-color: var(--blue); box-shadow: 0 0 0 3px var(--blue-soft); }
        .hint { font-size: 12.5px; color: var(--muted); margin-top: 6px; }
        .grid { display: grid; gap: 16px; }
        .g2 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .g4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .flash { background: var(--green-soft); color: var(--green); border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; font-weight: 500; }
        .errors { background: var(--red-soft); color: var(--red); border-radius: 12px; padding: 12px 16px; margin-bottom: 16px; }
        table.t { width: 100%; border-collapse: collapse; }
        table.t th { text-align: left; font-size: 12px; text-transform: uppercase; letter-spacing: .6px; color: var(--muted); font-weight: 600; padding: 10px 12px; border-bottom: 1px solid var(--line); }
        table.t td { padding: 12px; border-bottom: 1px solid var(--line); vertical-align: middle; }
        table.t tr:last-child td { border-bottom: 0; }
        .tbl-wrap { overflow-x: auto; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; background: var(--blue-soft); color: var(--blue); }
        .badge.green { background: var(--green-soft); color: var(--green); } .badge.amber { background: var(--amber-soft); color: var(--amber); } .badge.red { background: var(--red-soft); color: var(--red); }
        .muted { color: var(--muted); }
        .row-acts { display: flex; gap: 6px; justify-content: flex-end; flex-wrap: wrap; }
        .row-acts form { margin: 0; }
        .empty { text-align: center; color: var(--muted); padding: 30px; }
        nav[role="navigation"] svg { width: 16px; } .pager { margin-top: 14px; }
        .pager nav > div:first-child { display: none; }
        .pager nav span, .pager nav a { display: inline-block; padding: 6px 11px; border: 1px solid var(--line); border-radius: 8px; margin: 2px; background: var(--panel); }
        .pager p { color: var(--muted); }

        @media (max-width: 960px) {
            .side { transform: translateX(-100%); } body.nav-open .side { transform: none; box-shadow: 0 0 0 100vmax rgba(0,0,0,.35); }
            main { margin-left: 0; padding: 16px; }
            .topbar { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; font-weight: 700; }
            .g4 { grid-template-columns: repeat(2, minmax(0, 1fr)); } .g2 { grid-template-columns: 1fr; }
        }
    </style>
    @stack('styles')
    @include('partials.theme-anim')
</head>
<body>
@php($r = request()->route()?->getName() ?? '')
<aside class="side">
    @php($panel = session('panel', 'reading'))
    <style>
        .side-logo { position: relative; display: flex; align-items: center; justify-content: center; margin: 2px 10px 18px; padding: 14px 12px;
            border-radius: 18px; background: #fff; text-decoration: none; overflow: hidden;
            box-shadow: 0 1px 0 rgba(255,255,255,.6) inset, 0 10px 24px -14px rgba(15,23,42,.35); border: 1px solid #E6EAF2;
            transition: transform .25s, box-shadow .25s; }
        .side-logo::before { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; background: linear-gradient(90deg, #1E3A8A, #2563EB, #E2263C); }
        .side-logo::after { content: ''; position: absolute; top: 0; left: -60%; width: 40%; height: 100%;
            background: linear-gradient(100deg, transparent, rgba(255,255,255,.7), transparent); transform: skewX(-20deg); transition: left .7s; }
        .side-logo:hover { transform: translateY(-2px); box-shadow: 0 16px 30px -16px rgba(37,99,235,.55); }
        .side-logo:hover::after { left: 130%; }
        .side-logo img { display: block; width: 150px; max-width: 100%; height: auto; }
        /* Dark mode: see-through frosted glass instead of a solid white box */
        :root[data-theme="dark"] .side-logo { background: rgba(255,255,255,.07); border-color: rgba(255,255,255,.12);
            backdrop-filter: blur(8px); box-shadow: 0 14px 30px -16px rgba(0,0,0,.8); }
        :root[data-theme="dark"] .side-logo::after { background: linear-gradient(100deg, transparent, rgba(255,255,255,.18), transparent); }
        /* Dark mode uses the logo version with white letters (red parts unchanged) */
        .side-logo .lg-dark { display: none; }
        :root[data-theme="dark"] .side-logo .lg-light { display: none; }
        :root[data-theme="dark"] .side-logo .lg-dark { display: block; }
    </style>
    <a href="{{ route('admin.dashboard') }}" class="side-logo" title="Hexa's Zindabazar"><img class="lg-light" src="{{ asset('images/new-logo.png') }}" alt="Hexa's Zindabazar"><img class="lg-dark" src="{{ asset('images/new-logo-dark.png') }}" alt="Hexa's Zindabazar"></a>
    <div class="brand"><span class="logo"><span class="material-icons-outlined">{{ $panel === 'writing' ? 'edit_note' : 'menu_book' }}</span></span><span style="font-size:17px">{{ ucfirst($panel) }} for <b>IELTS</b></span></div>
    <div class="menu-label">MENU</div>
    <nav class="menu">
        @php($admin = auth()->user()->isAdmin())
        <details @if ($r === 'admin.dashboard') open @endif>
            <summary class="m-item"><span class="material-icons-outlined">home</span>Dashboards<span class="material-icons-outlined chev">chevron_right</span></summary>
            <div class="m-sub"><a href="{{ route('admin.dashboard') }}" class="{{ $r === 'admin.dashboard' ? 'on' : '' }}">Analytics</a></div>
        </details>
        @if ($panel === 'reading')
        <details open>
            <summary class="m-item"><span class="material-icons-outlined">menu_book</span>Reading<span class="material-icons-outlined chev">chevron_right</span></summary>
            <div class="m-sub">
                @if ($admin)<a href="{{ route('admin.reading.answers') }}" class="{{ $r === 'admin.reading.answers' ? 'on' : '' }}">Create Answer</a>@endif
                <a href="{{ route('admin.reading.results') }}" class="{{ in_array($r, ['admin.reading.results', 'admin.reading.show']) ? 'on' : '' }}">Result List</a>
            </div>
        </details>
        @else
        <details open>
            <summary class="m-item"><span class="material-icons-outlined">edit_note</span>Writing<span class="material-icons-outlined chev">chevron_right</span></summary>
            <div class="m-sub"><a href="{{ route('admin.writing.index') }}" class="{{ str_starts_with($r, 'admin.writing') ? 'on' : '' }}">Submissions</a></div>
        </details>
        @endif
        <a href="{{ route('admin.batches.index') }}" class="m-item {{ str_starts_with($r, 'admin.batches') ? 'on' : '' }}"><span class="material-icons-outlined">lock_open</span>{{ $admin ? 'Batches & Locks' : 'My Batches' }}</a>
        @if ($admin)
        <a href="{{ route('admin.promos.index') }}" class="m-item {{ str_starts_with($r, 'admin.promos') ? 'on' : '' }}"><span class="material-icons-outlined">campaign</span>Promo Popups</a>
        <details @if (str_starts_with($r, 'admin.students')) open @endif>
            <summary class="m-item"><span class="material-icons-outlined">person_add</span>Register<span class="material-icons-outlined chev">chevron_right</span></summary>
            <div class="m-sub">
                @php($listRole = $r === 'admin.students.index' ? request('role', 'student') : '')
                <a href="{{ route('admin.students.create') }}" class="{{ $r === 'admin.students.create' ? 'on' : '' }}">Add User</a>
                <a href="{{ route('admin.students.index', ['role' => 'student']) }}" class="{{ $listRole === 'student' ? 'on' : '' }}">Student List</a>
                <a href="{{ route('admin.students.index', ['role' => 'teacher']) }}" class="{{ $listRole === 'teacher' ? 'on' : '' }}">Teacher List</a>
                <a href="{{ route('admin.students.index', ['role' => 'admin']) }}" class="{{ $listRole === 'admin' ? 'on' : '' }}">Admin List</a>
            </div>
        </details>
        <a href="{{ route('admin.assign.index') }}" class="m-item {{ str_starts_with($r, 'admin.assign') ? 'on' : '' }}"><span class="material-icons-outlined">assignment_ind</span>Assign Batch</a>
        @endif
        @if ($admin)<a href="{{ route('admin.choose') }}" class="m-item"><span class="material-icons-outlined">swap_horiz</span>Switch panel</a>@endif
        <a href="{{ route('home') }}" class="m-item" target="_blank"><span class="material-icons-outlined">open_in_new</span>View Site</a>
    </nav>
    <div class="me">
        <div class="me-row">
            <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <div><b>{{ auth()->user()->name }}</b><small>{{ \App\Models\User::ROLES[auth()->user()->role] ?? '' }}</small></div>
        </div>
        <button type="button" class="theme-tg" id="themeTg"><span><span class="material-icons-outlined" style="font-size:18px">light_mode</span> <span class="tl">Light mode</span></span><span class="switch"></span></button>
        <form method="POST" action="{{ route('logout') }}">@csrf<button class="logout" style="width:100%;cursor:pointer;font:inherit"><span class="material-icons-outlined" style="font-size:18px">logout</span>Log out</button></form>
    </div>
</aside>

<main>
    <div class="topbar"><button class="btn ghost sm" type="button" onclick="document.body.classList.toggle('nav-open')"><span class="material-icons-outlined">menu</span></button>{{ ucfirst(session('panel', 'reading')) }} for IELTS Admin</div>
    @if (session('ok'))<div class="flash">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="errors">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
    @yield('content')
    <footer style="margin-top:24px;border-top:1px solid var(--line);color:var(--muted)">@include('partials.credit')</footer>
</main>

<script>
(function () {
    var root = document.documentElement, tg = document.getElementById('themeTg');
    function label() { tg.querySelector('.tl').textContent = root.dataset.theme === 'dark' ? 'Dark mode' : 'Light mode'; }
    tg.addEventListener('click', function () {
        hxSwitchTheme(tg.querySelector('.switch'), function () {
            root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            try { localStorage.setItem('admin-theme', root.dataset.theme); } catch (e) {}
            label();
        });
    });
    label();
    document.addEventListener('click', function (e) {
        if (document.body.classList.contains('nav-open') && !e.target.closest('.side, .topbar')) document.body.classList.remove('nav-open');
    });
    document.querySelectorAll('form[data-confirm]').forEach(function (f) {
        f.addEventListener('submit', function (e) { if (!confirm(f.dataset.confirm)) e.preventDefault(); });
    });
})();
</script>
@stack('scripts')
</body>
</html>
