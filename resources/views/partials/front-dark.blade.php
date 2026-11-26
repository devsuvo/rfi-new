{{-- Dark mode for the student-facing pages (front_end.layout.app). Toggled by partials.theme-toggle; shares the 'admin-theme' key with the admin panel. --}}
<style>
    .lg-dark { display: none !important; }
    :root[data-theme="dark"] .lg-light { display: none !important; }
    :root[data-theme="dark"] .lg-dark { display: block !important; }

    /* Toggle sits in the header beside the profile menu */
    .site-header .hdr-right { display: flex; align-items: center; gap: 10px; }
    .site-header .theme-fab { position: static; width: 40px; height: 40px; box-shadow: 0 1px 2px rgba(10,22,64,.05); }

    :root[data-theme="dark"] {
        color-scheme: dark;
        --bg: #0D1324; --panel: #151C31; --panel-2: #1A2239; --ink: #E7ECF7; --muted: #94A0BC; --line: #26304A;
        --t-ink: #E7ECF7; --t-muted: #94A0BC; --t-line: #26304A; --t-bg: #1A2239; --hp-navy: #E7ECF7;
        --shadow-sm: 0 1px 2px rgba(0,0,0,.3); --shadow-lg: 0 24px 48px -12px rgba(0,0,0,.6);
    }
    :root:not([data-theme="dark"]) { --panel: #fff; --line: #E6E9F2; --ink: #0B1433; }
    :root[data-theme="dark"] body { background: radial-gradient(700px 500px at 100% 0%, rgba(47,91,255,.10), transparent 70%), #0D1324; color: var(--t-ink); }
    :root[data-theme="dark"] ::selection { background: #3B4A80; color: #fff; }

    /* Header / footer / profile menu */
    :root[data-theme="dark"] .site-header { background: rgba(13,19,36,.82); border-bottom-color: #26304A; }
    :root[data-theme="dark"] .site-footer, :root[data-theme="dark"] .site-footer .tag { color: #94A0BC; }
    :root[data-theme="dark"] .site-footer .wrap { border-top-color: #26304A; }
    :root[data-theme="dark"] .user-btn { background: #151C31; border-color: #26304A; color: #94A0BC; }
    :root[data-theme="dark"] .user-drop { background: rgba(21,28,49,.97); border-color: #26304A; }
    :root[data-theme="dark"] .user-name { color: #E7ECF7; border-bottom-color: #26304A; }
    :root[data-theme="dark"] .user-name small { color: #94A0BC !important; }
    :root[data-theme="dark"] .user-drop a { color: #E7ECF7; }
    :root[data-theme="dark"] .user-drop a .material-icons-outlined { color: #94A0BC; }
    :root[data-theme="dark"] .user-drop a:hover { background: #1A2239; }
    :root[data-theme="dark"] .user-drop a.logout:hover, :root[data-theme="dark"] .user-drop a.logout:hover .material-icons-outlined { background: #3A1D25; color: #F26B6B; }

    /* Home page */
    :root[data-theme="dark"] .hp-pill { background: #1D2A4D; border-color: #2B3A66; color: #C9D6FF; }
    :root[data-theme="dark"] .hp-title, :root[data-theme="dark"] .hp-batch-name, :root[data-theme="dark"] .hp-modules-head h2 { color: #F1F4FB; }
    :root[data-theme="dark"] .hp-lead { color: #B7C1D9; }
    :root[data-theme="dark"] .hp-modules-head p, :root[data-theme="dark"] .hp-tagline, :root[data-theme="dark"] .hp-batch-label, :root[data-theme="dark"] .btns .num { color: #94A0BC; }
    :root[data-theme="dark"] .hp-batch, :root[data-theme="dark"] .btns { background: #151C31; border-color: #26304A; color: #E7ECF7; }
    :root[data-theme="dark"] .btns::after { background: linear-gradient(100deg, transparent, rgba(255,255,255,.08), transparent); }

    /* Module index (class list) */
    :root[data-theme="dark"] .mi { --soft: color-mix(in srgb, var(--tone) 18%, #151C31); }
    :root[data-theme="dark"] .mi-hero .wrap { background: linear-gradient(100deg, #151C31 45%, #12323A); border-color: #26304A; box-shadow: 0 24px 50px -36px rgba(0,0,0,.8); }
    :root[data-theme="dark"] .mi-head h1, :root[data-theme="dark"] .card h3, :root[data-theme="dark"] .card.k-start h3, :root[data-theme="dark"] .card::after, :root[data-theme="dark"] .mi-stats b { color: #E7ECF7; }
    :root[data-theme="dark"] .back, :root[data-theme="dark"] .back:hover, :root[data-theme="dark"] .mi-stats div { background: #1A2239; border-color: #26304A; color: #C3CCE0; }
    :root[data-theme="dark"] .mi-art { filter: brightness(.9); }
    :root[data-theme="dark"] .card, :root[data-theme="dark"] .card.k-start { background: #151C31; border-color: #26304A; }
    :root[data-theme="dark"] a.card:hover { box-shadow: 0 22px 40px -22px rgba(0,0,0,.8); }
    :root[data-theme="dark"] a.card:hover .go, :root[data-theme="dark"] a.card.k-start:hover .go { background: var(--tone); border-color: var(--tone); color: #fff; }
    :root[data-theme="dark"] .card.k-test .ic, :root[data-theme="dark"] .card.k-test .tag { background: #36290F; color: #F2C14E; }
    :root[data-theme="dark"] .card.k-test::before { background: #2A2210; }
    :root[data-theme="dark"] .card.is-locked, :root[data-theme="dark"] .lock-wrap:has(.unlock-btn:not(.is-on)) .card { background: #111829; }
    :root[data-theme="dark"] .card.is-locked::before { background: #1A2239; }
    :root[data-theme="dark"] .card.is-locked .ic { background: #1F2840; color: #6B7896; }
    :root[data-theme="dark"] .card.is-locked h3, :root[data-theme="dark"] .card.is-locked .eyebrow, :root[data-theme="dark"] .card.is-locked::after { color: #6B7896; }
    :root[data-theme="dark"] .badge.locked { background: #1F2840 !important; color: #94A0BC !important; }
    :root[data-theme="dark"] .lock-flash { background: #36290F; color: #F2C14E; }
    :root[data-theme="dark"] .lock-flash.ok-flash { background: #15301F; color: #5FD68E; border-color: #1F4D30; }

    /* Teacher / student bars, controls */
    :root[data-theme="dark"] .teacher-bar, :root[data-theme="dark"] .t-ctl, :root[data-theme="dark"] .tool, :root[data-theme="dark"] .bulk button,
    :root[data-theme="dark"] .hw-quick button, :root[data-theme="dark"] .stepper button { background: #151C31; border-color: #26304A; color: #C3CCE0; }
    :root[data-theme="dark"] .teacher-bar p, :root[data-theme="dark"] .batch-chip, :root[data-theme="dark"] .bulk .cnt, :root[data-theme="dark"] .tb-all, :root[data-theme="dark"] .hw-form label { color: #C3CCE0; }
    :root[data-theme="dark"] .tool.muted { color: #6B7896; }
    :root[data-theme="dark"] .card-box .unlock-btn { background: rgba(21,28,49,.9); border-color: #34405E; color: #C3CCE0; }
    :root[data-theme="dark"] .card-box .unlock-btn:hover { background: #151C31; }
    :root[data-theme="dark"] .card-box .unlock-btn.is-on { background: rgba(21,48,31,.92); color: #5FD68E; }
    :root[data-theme="dark"] .card-box .unlock-btn.is-on:hover { background: #3A1D25; color: #F26B6B; }
    :root[data-theme="dark"] .unlock-btn { background: #3A1D25; border-color: #5A2A35; color: #F28B9A; }
    :root[data-theme="dark"] .unlock-btn.is-on { background: #15301F; border-color: #1F4D30; color: #5FD68E; }
    :root[data-theme="dark"] .unlock-btn .sw { background: #34405E; }

    /* Homework */
    :root[data-theme="dark"] .hw-alert { background: linear-gradient(135deg, #2E220D, #36290F); border-color: #5A4316; }
    :root[data-theme="dark"] .hw-alert-txt { color: #E9C77F; }
    :root[data-theme="dark"] .hw-alert-txt > b { color: #FBE3AE; }
    :root[data-theme="dark"] .hw-alert-x { color: #E9C77F; }
    /* "Your homework" panel: its own light / dark colours live with it in module-index */
    :root[data-theme="dark"] .hw-item small { color: #94A0BC; }
    :root[data-theme="dark"] .hw-when, :root[data-theme="dark"] .hw-cd-chip, :root[data-theme="dark"] .hw-badge, :root[data-theme="dark"] .hw-tool.is-set { background: #36290F; color: #F2C14E; border-color: #5A4316; }
    :root[data-theme="dark"] .hw-badge.late, :root[data-theme="dark"] .hw-cd-chip.soon, :root[data-theme="dark"] .hw-tool.is-late { background: #3A1D25; color: #F26B6B; border-color: #5A2A35; }

    /* Dialogs + forms */
    :root[data-theme="dark"] .mi-box { background: #151C31; }
    :root[data-theme="dark"] .mi-box p { color: #94A0BC; }
    :root[data-theme="dark"] .mi-acts button { background: #1F2840; color: #C3CCE0; }
    :root[data-theme="dark"] .mi-acts .b-yes { background: var(--tone); color: #fff; }
    :root[data-theme="dark"] .mi-acts .b-remove { background: #3A1D25; color: #F26B6B; }
    :root[data-theme="dark"] .mi-toast { background: #E7ECF7; color: #0D1324; }
    :root[data-theme="dark"] .hw-form input, :root[data-theme="dark"] .hw-form textarea, :root[data-theme="dark"] .stepper input { background: #0D1324; border-color: #26304A; color: #E7ECF7; }

    /* My results / result detail / choose batch */
    :root[data-theme="dark"] .mr-row, :root[data-theme="dark"] .mr-nav a, :root[data-theme="dark"] .mr .back, :root[data-theme="dark"] .box,
    :root[data-theme="dark"] .empty, :root[data-theme="dark"] .cb-grid button { background: #151C31; border-color: #26304A; }
    :root[data-theme="dark"] .mr-ic.reading { background: #12323A; color: #3CC8D8; }
    :root[data-theme="dark"] .mr-ic.writing { background: #3A1D25; color: #F26B6B; }
    :root[data-theme="dark"] .pill-wait { background: #36290F; color: #F2C14E; }
    :root[data-theme="dark"] .q.ok { background: #15301F; color: #5FD68E; }
    :root[data-theme="dark"] .q.no { background: #3A1D25; color: #F26B6B; }
    :root[data-theme="dark"] .q[style] { background: #1A2239 !important; color: #5FD68E !important; }
    :root[data-theme="dark"] .fb { background: #2A2210; }
    :root[data-theme="dark"] .cb .pill-w, :root[data-theme="dark"] .b-icon { background: #1D2A4D; }
    :root[data-theme="dark"] .cb-grid button.current { background: #18213D; }
</style>
