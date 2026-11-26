<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Choose panel · IELTS Admin</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script>try { if (localStorage.getItem('admin-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}</script>
    <style>
        :root { --bg: #F4F6FB; --panel: #fff; --ink: #111A33; --muted: #6B7590; --line: #E4E8F1; }
        :root[data-theme="dark"] { --bg: #0D1324; --panel: #151C31; --ink: #E7ECF7; --muted: #94A0BC; --line: #26304A; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px 16px; font-family: 'Poppins', system-ui, sans-serif; background: var(--bg); color: var(--ink); }
        .wrap { width: min(760px, 100%); text-align: center; }
        h1 { font-size: 26px; font-weight: 600; margin: 0 0 6px; }
        p.sub { color: var(--muted); margin: 0 0 30px; }
        .opts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 20px; }
        .opt { display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 36px 20px; background: var(--panel); border: 1px solid var(--line); border-radius: 22px; color: inherit; text-decoration: none; transition: transform .2s, box-shadow .2s, border-color .2s; }
        .opt:hover { transform: translateY(-4px); border-color: var(--tone); box-shadow: 0 20px 40px -16px var(--tone); }
        .opt .ic { width: 72px; height: 72px; border-radius: 20px; display: grid; place-items: center; background: var(--soft); color: var(--tone); }
        .opt .ic span { font-size: 38px; }
        .opt b { font-size: 20px; } .opt b i { font-style: normal; color: var(--tone); }
        .opt small { color: var(--muted); }
        .out { display: inline-block; margin-top: 26px; color: var(--muted); background: none; border: 0; font: inherit; cursor: pointer; text-decoration: underline; }
        @media (max-width: 560px) { .opts { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
@include('partials.theme-toggle')
<div class="wrap">
    <h1>Welcome, {{ auth()->user()->name }}</h1>
    <p class="sub">Which panel do you want to open?</p>
    <div class="opts">
        <a class="opt" href="{{ route('admin.panel', 'reading') }}" style="--tone:#2563EB;--soft:#E8EFFE">
            <span class="ic"><span class="material-icons-outlined">menu_book</span></span>
            <b>Reading for <i>IELTS</i></b>
            <small>Answer keys · Results</small>
        </a>
        <a class="opt" href="{{ route('admin.panel', 'writing') }}" style="--tone:#E2263C;--soft:#FDE9EC">
            <span class="ic"><span class="material-icons-outlined">edit_note</span></span>
            <b>Writing for <i>IELTS</i></b>
            <small>Submissions · Band marking</small>
        </a>
    </div>
    <form method="POST" action="{{ route('logout') }}">@csrf<button class="out">Log out</button></form>
</div>
<footer style="position:fixed;left:0;right:0;bottom:0;color:#64748B">@include('partials.credit')</footer>
</body>
</html>
