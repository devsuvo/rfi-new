<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · IELTS Practice Portal</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script>try { if (localStorage.getItem('admin-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}</script>
    <style>
        :root { --bg: #F4F6FB; --panel: #fff; --field: #F7F9FD; --ink: #111A33; --muted: #6B7590; --line: #E4E8F1; --blue: #2563EB; --blue-2: #4F46E5; --blue-soft: #E8EFFE; --red: #DC2626; --red-soft: #FDECEC; }
        :root[data-theme="dark"] { --bg: #0B1120; --panel: #121A2E; --field: #0F1628; --ink: #E7ECF7; --muted: #94A0BC; --line: #233050; --blue: #5B8CFF; --blue-2: #7C6CFF; --blue-soft: #1D2A4D; --red: #F26B6B; --red-soft: #3A1D25; }
        .lg-dark { display: none !important; }
        :root[data-theme="dark"] .lg-light { display: none !important; }
        :root[data-theme="dark"] .lg-dark { display: block !important; }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Poppins', system-ui, sans-serif; background: var(--bg); color: var(--ink); display: grid; place-items: center; padding: 24px 16px 64px; }

        .shell { width: min(980px, 100%); display: grid; grid-template-columns: 1.05fr 1fr; background: var(--panel); border: 1px solid var(--line); border-radius: 28px; overflow: hidden; box-shadow: 0 40px 80px -32px rgba(17,26,51,.28); animation: rise .5s ease both; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        /* Left: brand panel */
        .hero { position: relative; padding: 44px 40px; color: #fff; background: linear-gradient(145deg, #1E3A8A 0%, #2563EB 55%, #4F46E5 100%); display: flex; flex-direction: column; justify-content: space-between; gap: 32px; overflow: hidden; }
        .hero::before, .hero::after { content: ""; position: absolute; border-radius: 50%; background: rgba(255,255,255,.08); }
        .hero::before { width: 320px; height: 320px; right: -110px; top: -110px; }
        .hero::after { width: 220px; height: 220px; left: -80px; bottom: -90px; }
        .hero > * { position: relative; z-index: 1; }
        .pill { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); font-size: 12.5px; font-weight: 500; backdrop-filter: blur(4px); }
        .pill .material-icons-outlined { font-size: 16px; }
        .hero h1 { font-size: 34px; line-height: 1.2; margin: 18px 0 12px; font-weight: 700; }
        .hero h1 span { color: #FDE68A; }
        .hero p { margin: 0; color: rgba(255,255,255,.82); font-size: 14.5px; line-height: 1.65; max-width: 360px; }
        .feats { display: grid; gap: 12px; }
        .feat { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 14px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.14); font-size: 14px; }
        .feat .ic { width: 36px; height: 36px; flex: none; border-radius: 10px; background: rgba(255,255,255,.18); display: grid; place-items: center; }
        .feat .ic .material-icons-outlined { font-size: 20px; }
        .feat small { display: block; color: rgba(255,255,255,.72); font-size: 12px; }

        /* Right: form */
        .box { padding: 44px 40px; display: flex; flex-direction: column; justify-content: center; }
        .box img { height: 52px; width: auto; max-width: 100%; object-fit: contain; align-self: flex-start; margin: 0 0 22px; }
        .login-logo { align-self: flex-start; margin: 0 0 22px; }
        .box h2 { margin: 0 0 4px; font-size: 24px; font-weight: 700; }
        .box h2 b { background: linear-gradient(90deg, var(--blue), var(--blue-2)); -webkit-background-clip: text; background-clip: text; color: transparent; }
        p.sub { color: var(--muted); margin: 0 0 26px; font-size: 14.5px; }
        label.lb { display: block; font-size: 13.5px; font-weight: 500; margin: 0 0 7px; }
        .field { position: relative; margin-bottom: 18px; }
        .field > .material-icons-outlined { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 20px; pointer-events: none; transition: color .2s; }
        .field input { width: 100%; border: 1.5px solid var(--line); border-radius: 14px; padding: 13px 46px 13px 44px; font: inherit; font-size: 15px; background: var(--field); color: var(--ink); transition: border-color .2s, box-shadow .2s, background .2s; }
        .field input:-webkit-autofill, .field input:-webkit-autofill:hover, .field input:-webkit-autofill:focus { -webkit-text-fill-color: var(--ink); caret-color: var(--ink); -webkit-box-shadow: 0 0 0 1000px var(--field) inset; transition: background-color 9999s ease 0s; }
        .field input:-webkit-autofill:focus { -webkit-box-shadow: 0 0 0 1000px var(--panel) inset, 0 0 0 4px var(--blue-soft); }
        .field input::placeholder { color: var(--muted); opacity: .7; }
        .field input:focus { outline: none; border-color: var(--blue); background: var(--panel); box-shadow: 0 0 0 4px var(--blue-soft); }
        .field:focus-within > .material-icons-outlined { color: var(--blue); }
        .eye { position: absolute; right: 8px; top: 50%; transform: translateY(-50%); border: 0; background: none; color: var(--muted); cursor: pointer; padding: 6px; border-radius: 8px; display: grid; place-items: center; }
        .eye:hover { color: var(--ink); background: var(--blue-soft); }
        .eye .material-icons-outlined { font-size: 20px; }
        .rem { display: flex; align-items: center; gap: 9px; margin: 4px 0 24px; color: var(--muted); font-size: 14px; cursor: pointer; user-select: none; }
        .rem input { width: 17px; height: 17px; accent-color: var(--blue); cursor: pointer; }
        .go { width: 100%; border: 0; border-radius: 14px; padding: 14px; font: inherit; font-weight: 600; font-size: 15px; color: #fff; cursor: pointer; background: linear-gradient(90deg, var(--blue), var(--blue-2)); box-shadow: 0 12px 24px -10px var(--blue); display: flex; align-items: center; justify-content: center; gap: 8px; transition: transform .15s, box-shadow .2s, filter .2s; }
        .go:hover { transform: translateY(-1px); filter: brightness(1.06); box-shadow: 0 16px 28px -10px var(--blue); }
        .go:active { transform: none; }
        .go .material-icons-outlined { font-size: 20px; transition: transform .2s; }
        .go:hover .material-icons-outlined { transform: translateX(3px); }
        .err { display: flex; align-items: center; gap: 8px; background: var(--red-soft); color: var(--red); border-radius: 12px; padding: 11px 14px; font-size: 14px; margin-bottom: 18px; }
        .err .material-icons-outlined { font-size: 20px; }
        .help { margin: 22px 0 0; text-align: center; color: var(--muted); font-size: 13px; }

        footer { position: fixed; left: 0; right: 0; bottom: 0; color: #64748B; }

        @media (max-width: 820px) {
            .shell { grid-template-columns: 1fr; max-width: 440px; }
            .hero { display: none; }
            .box { padding: 32px 24px; }
        }
        @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    </style>
</head>
<body>
@include('partials.theme-toggle')
<main class="shell">
    <aside class="hero">
        <div>
            <span class="pill"><span class="material-icons-outlined">school</span> Hexa's Zindabazar</span>
            <h1>Your road to a <span>higher band</span> starts here.</h1>
            <p>Practise with real exam-style tests, track your progress and get feedback from your teachers, all in one place.</p>
        </div>
        <div class="feats">
            <div class="feat"><span class="ic"><span class="material-icons-outlined">quiz</span></span><div>Exam-style practice tests<small>Same format as the real IELTS</small></div></div>
            <div class="feat"><span class="ic"><span class="material-icons-outlined">insights</span></span><div>Track your results<small>See your scores and bands over time</small></div></div>
            <div class="feat"><span class="ic"><span class="material-icons-outlined">rate_review</span></span><div>Teacher feedback<small>Personal guidance to improve faster</small></div></div>
        </div>
    </aside>

    <form class="box" method="POST" action="{{ url('/login') }}">
        @csrf
        <div class="login-logo">@include('partials.hexas-logo', ['size' => 50])</div>
        <h2>Welcome back to <b>IELTS</b></h2>
        <p class="sub">Sign in to the IELTS Practice Portal to continue.</p>

        @error('email')<div class="err"><span class="material-icons-outlined">error_outline</span>{{ $message }}</div>@enderror

        <label class="lb" for="email">Email</label>
        <div class="field">
            <span class="material-icons-outlined">mail</span>
            <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="you@example.com" autocomplete="email" required autofocus>
        </div>

        <label class="lb" for="password">Password</label>
        <div class="field">
            <span class="material-icons-outlined">lock</span>
            <input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required>
            <button type="button" class="eye" aria-label="Show password" onclick="var i=document.getElementById('password'),s=i.type==='password';i.type=s?'text':'password';this.firstElementChild.textContent=s?'visibility_off':'visibility';this.setAttribute('aria-label',s?'Hide password':'Show password');"><span class="material-icons-outlined">visibility</span></button>
        </div>

        <label class="rem"><input type="checkbox" name="remember" value="1"> Remember me</label>
        <button class="go" type="submit">Log in <span class="material-icons-outlined">arrow_forward</span></button>
        <p class="help">Trouble signing in? Please contact your teacher.</p>
    </form>
</main>
<footer>@include('partials.credit')</footer>
</body>
</html>
