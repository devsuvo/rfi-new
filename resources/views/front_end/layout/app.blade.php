<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'IELTS Practice Portal') · Hexa's Zindabazar</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Caveat:wght@500;600&family=Instrument+Serif:ital@0;1&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
    <script>try { if (localStorage.getItem('admin-theme') === 'dark') document.documentElement.dataset.theme = 'dark'; } catch (e) {}</script>
    <style>
        :root {
            --hp-navy: #0A1640; --hp-navy-2: #13235E; --hp-blue: #2F5BFF; --hp-red: #E2263C; --hp-gold: #E8B84A; --hp-gold-2: #F6D88A;
            --t-ink: #0B1433; --t-muted: #5E6A85; --t-line: #E6E9F2; --t-bg: #F7F7FB;
            --sans: 'Poppins', 'Hind Siliguri', system-ui, sans-serif;
            --serif: 'Instrument Serif', Georgia, serif;
            --shadow-sm: 0 1px 2px rgba(10,22,64,.04), 0 2px 8px rgba(10,22,64,.04);
            --shadow-lg: 0 2px 4px rgba(10,22,64,.04), 0 24px 48px -12px rgba(10,22,64,.18);
            --ease: cubic-bezier(.2,.8,.2,1);
            --wrap: min(1240px, calc(100% - 48px));
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            font-family: var(--sans); color: var(--t-ink);
            background: radial-gradient(900px 520px at 78% 18%, rgba(255,255,255,.95), transparent 70%), radial-gradient(700px 500px at 100% 100%, rgba(29,91,255,.06), transparent 70%), linear-gradient(180deg, #F3F6FB 0%, #F7F9FC 55%, #FFFFFF 100%);
            min-height: 100vh; display: flex; flex-direction: column;
            -webkit-font-smoothing: antialiased; text-rendering: optimizeLegibility;
        }
        body, body *:not(.material-icons-outlined):not(.serif) { font-family: var(--sans) !important; }
        .serif { font-family: var(--serif) !important; font-weight: 400; font-style: italic; letter-spacing: 0; }
        ::selection { background: #FFE89A; color: var(--t-ink); }
        a { color: inherit; text-decoration: none; }
        .wrap { width: var(--wrap); margin-inline: auto; }
        @media (max-width: 520px) { :root { --wrap: calc(100% - 32px); } }

        /* Header */
        .site-header { position: sticky; top: 0; z-index: 50; background: rgba(255,255,255,.82); backdrop-filter: saturate(180%) blur(18px); -webkit-backdrop-filter: saturate(180%) blur(18px); border-bottom: 1px solid rgba(10,22,64,.06); }
        .site-header .wrap { display: flex; align-items: center; justify-content: space-between; height: 76px; }
        .site-header .logo img { display: block; transition: opacity .2s; }
        .site-header .logo:hover img { opacity: .8; }

        /* Footer */
        .site-footer { margin-top: auto; background: transparent; color: #5B6B86; }
        .site-footer .wrap { border-top: 1px solid #E7ECF4; }
        .site-footer .wrap { display: flex; flex-wrap: wrap; gap: 8px 24px; justify-content: space-between; align-items: center; padding: 26px 0; font-size: 13px; }
        .site-footer .tag { display: flex; align-items: center; gap: 14px; letter-spacing: .2em; font-weight: 500; font-size: 11.5px; color: #7B8499; }
        .site-footer .tag::before { content: ''; width: 18px; height: 1px; background: #9AA3B5; }

        @keyframes rise { from { opacity: 0; transform: translateY(14px); } }
        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { animation: none !important; transition: none !important; } }
    </style>
    @stack('styles')
    @include('partials.front-dark')
</head>
<body>
    <header class="site-header">
        <div class="wrap">
            <a href="{{ route('home') }}" class="logo" aria-label="Hexa's Zindabazar home">
                @include('partials.hexas-logo', ['size' => 40])
            </a>
            <div class="hdr-right">
                @include('partials.theme-toggle')
                @include('front_end.layout.user')
            </div>
        </div>
    </header>

    <main>@yield('content')</main>

    <footer class="site-footer">
        <div class="wrap">
            <span class="tag">LEARN &nbsp;|&nbsp; PRACTICE &nbsp;|&nbsp; IMPROVE &nbsp;|&nbsp; SUCCEED</span>
        </div>
        @include('partials.credit')
    </footer>
    @include('front_end.layout.promo')
    @stack('scripts')
</body>
</html>
