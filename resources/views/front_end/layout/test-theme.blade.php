{{--
    Shared theme for every test page.
    @include('front_end.layout.test-theme', ['tone' => '#0aa3b5', 'soft' => '#e3f6f8', 'module' => 'Reading', 'minutes' => 60])
    Page markup: .t-head card, then .t-tabs with one <section class="t-pane" data-tab="Name 1"> per passage,
    each holding .t-passage (left) and .t-questions (right).
--}}
@php
    $tone = $tone ?? '#1D5BFF';
    $soft = $soft ?? '#E8EFFF';
    $module = $module ?? 'Test';
    $minutes = $minutes ?? 60;
    $audio = $audio ?? false;
    $noTimer = $noTimer ?? false;   // class exercises are untimed; only homework has a clock
@endphp
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@400;500;600;700;800&family=Instrument+Serif:ital@0;1&family=Hind+Siliguri:wght@400;600&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet">
<style>
    :root {
        --t-tone: {{ $tone }}; --t-soft: {{ $soft }};
        --hp-navy: #0A1640; --hp-blue: #2F5BFF; --hp-red: #E2263C; --hp-gold: #E8B84A; --hp-gold-2: #F6D88A;
        --t-ink: #0B1433; --t-muted: #5E6A85; --t-line: #E6E9F2; --t-bg: #F3F4F9;
        --t-top: 60px; --t-bar: 64px;
        --shadow-sm: 0 1px 2px rgba(10,22,64,.04), 0 4px 14px rgba(10,22,64,.05);
        --shadow-lg: 0 2px 4px rgba(10,22,64,.05), 0 24px 48px -12px rgba(10,22,64,.24);
        --ease: cubic-bezier(.2,.8,.2,1);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; height: 100%; }
    body { background: var(--t-bg); color: var(--t-ink); font-size: 15px; line-height: 1.7; overflow: hidden; -webkit-font-smoothing: antialiased; }
    body, body *:not(.material-icons-outlined) { font-family: 'Plus Jakarta Sans', 'Hind Siliguri', system-ui, sans-serif !important; }
    button { cursor: pointer; font: inherit; }
    ::selection { background: #FFE89A; }
    * { scrollbar-width: thin; scrollbar-color: #CDD3E2 transparent; }

    /* Top bar */
    .t-topbar { height: var(--t-top); background: linear-gradient(90deg, #0A1640, #13235E); color: #fff; display: flex; align-items: center; gap: 14px; padding: 0 20px; position: relative; }
    .t-topbar::after { content: ''; position: absolute; left: 0; right: 0; bottom: 0; height: 2px; background: linear-gradient(90deg, transparent, var(--t-tone), var(--hp-gold), transparent); opacity: .8; }
    .t-topbar .logo { display: flex; background: #fff; border-radius: 10px; padding: 4px 10px; box-shadow: 0 4px 12px rgba(0,0,0,.2); }
    .t-timer { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 16px; font-variant-numeric: tabular-nums; margin-inline: auto; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); padding: 6px 16px 6px 12px; border-radius: 999px; letter-spacing: .5px; transition: background .3s, color .3s; }
    .t-timer .material-icons-outlined { font-size: 19px; color: var(--hp-gold-2); }
    .t-timer.low { color: var(--hp-gold-2); background: rgba(226,38,60,.25); border-color: rgba(226,38,60,.5); animation: pulse 2s ease-in-out infinite; }
    @keyframes pulse { 50% { box-shadow: 0 0 0 6px rgba(226,38,60,.12); } }
    .t-playing { display: flex; align-items: center; gap: 6px; font-size: 13px; opacity: .85; }
    .t-playing .material-icons-outlined { font-size: 18px; color: var(--hp-gold-2); }
    .t-btn { border: 0; border-radius: 12px; padding: 9px 16px; font-weight: 700; font-size: 14px; display: inline-flex; align-items: center; gap: 6px; transition: background .2s, transform .2s var(--ease), box-shadow .2s; }
    .t-btn:active { transform: scale(.97); }
    .t-btn.light { background: rgba(255,255,255,.1); color: #fff; border: 1px solid rgba(255,255,255,.12); }
    .t-btn.light:hover { background: rgba(255,255,255,.18); }
    .t-btn.light .c { min-width: 20px; height: 20px; border-radius: 10px; background: var(--hp-gold); color: var(--hp-navy); font-size: 11px; display: grid; place-items: center; padding: 0 5px; }
    .t-btn.primary { background: linear-gradient(180deg, var(--hp-gold-2), var(--hp-gold)); color: var(--hp-navy); box-shadow: 0 6px 18px -6px rgba(232,184,74,.7), inset 0 1px 0 rgba(255,255,255,.6); }
    .t-btn.primary:hover { box-shadow: 0 10px 24px -6px rgba(232,184,74,.8), inset 0 1px 0 rgba(255,255,255,.6); }
    .t-btn.tone { background: var(--t-tone); color: #fff; box-shadow: 0 10px 22px -8px var(--t-tone); padding: 12px 22px; }
    .t-btn .material-icons-outlined { font-size: 18px; }
    .t-notes { position: relative; }
    .t-notes-list { position: absolute; right: 0; top: calc(100% + 12px); width: 320px; max-height: 60vh; overflow: auto; background: #fff; color: var(--t-ink); border-radius: 18px; border: 1px solid var(--t-line); box-shadow: var(--shadow-lg); padding: 8px; z-index: 80; opacity: 0; visibility: hidden; transform: translateY(-6px); transition: opacity .2s, transform .25s var(--ease), visibility .2s; }
    .t-notes.open .t-notes-list { opacity: 1; visibility: visible; transform: none; }
    .t-notes-list .empty { color: var(--t-muted); font-size: 13px; padding: 14px; text-align: center; }
    .t-notes-list .item { padding: 10px 12px; border-radius: 12px; font-size: 13px; cursor: pointer; transition: background .15s; }
    .t-notes-list .item:hover { background: var(--t-bg); }
    .t-notes-list .item b { display: block; background: #FFF4D6; border-left: 3px solid var(--hp-gold); border-radius: 4px 8px 8px 4px; padding: 3px 8px; font-weight: 600; margin-bottom: 6px; }

    /* Header card */
    .t-head { margin: 14px 20px 0; background: #fff; border: 1px solid var(--t-line); border-radius: 16px; padding: 12px 18px; display: flex; align-items: center; gap: 12px; box-shadow: var(--shadow-sm); }
    .t-head .t-hw { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 999px; background: #FFF7E6; color: #B45309; border: 1px solid #FCD34D; font-size: 12.5px; font-weight: 700; white-space: nowrap; }
    .t-head .t-hw.late { background: #FEF2F2; color: #B91C1C; border-color: #FCA5A5; }
    .t-head .t-hw .material-icons-outlined { font-size: 16px; }
    .t-hw-note { margin: 10px 20px 0; padding: 10px 14px; border-radius: 12px; background: #FFFBF0; border: 1px solid #FCE3A6; color: #78350F; font-size: 14px; display: flex; gap: 8px; align-items: center; }
    .t-hw-note .material-icons-outlined { font-size: 18px; color: #D97706; }
    .t-head .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--t-tone); box-shadow: 0 0 0 4px var(--t-soft); }
    .t-head strong { font-weight: 800; color: var(--hp-navy); letter-spacing: -.01em; }
    .t-head span:not(.dot) { color: var(--t-muted); font-size: 14px; padding-left: 12px; border-left: 1px solid var(--t-line); }

    /* Two columns; both stop above the bottom tab bar */
    .t-tabs { position: absolute; left: 0; right: 0; top: calc(var(--t-top) + 72px); bottom: var(--t-bar); padding: 14px 20px; }
    .t-pane { display: none; height: 100%; grid-template-columns: 1fr 1fr; gap: 16px; }
    .t-pane.active { display: grid; animation: fade .35s var(--ease); }
    @keyframes fade { from { opacity: 0; transform: translateY(6px); } }
    .t-passage, .t-questions { overflow: auto; background: #fff; border: 1px solid var(--t-line); border-radius: 18px; padding: 26px 30px; position: relative; box-shadow: var(--shadow-sm); }
    .t-pane.single { grid-template-columns: 1fr; }
    .t-passage > :last-child, .t-questions > :last-child { margin-bottom: 0; }
    .t-questions > :first-child { margin-top: 0; }

    /* Headings */
    .t-passage h3 { font-size: 22px; font-weight: 800; color: var(--hp-navy); margin: 0 0 14px; letter-spacing: -.02em; line-height: 1.3; }
    .t-questions h4 { font-size: 16px; font-weight: 700; color: var(--hp-navy); margin: 30px 0 10px; padding-top: 22px; border-top: 1px dashed var(--t-line); }
    .t-questions h4:first-child { margin-top: 0; padding-top: 0; border-top: 0; }
    .topic-title { font-size: 17px !important; font-weight: 800; color: var(--hp-navy); margin: 0 0 10px; }
    .t-instr { color: var(--t-muted); margin: 0 0 14px; font-size: 14px; }
    .t-passage p { margin: 0 0 16px; line-height: 1.8; }
    .t-passage .para-label { display: inline-grid; place-items: center; min-width: 24px; height: 24px; border-radius: 7px; background: var(--t-soft); font-weight: 800; font-size: 13px; color: var(--t-tone); margin-right: 8px; vertical-align: 1px; }

    /* Inputs */
    .t-input, select.sel-answer { width: 200px; min-width: 200px; border: 1.5px solid var(--t-line); border-radius: 10px; padding: 5px 10px; font-size: 14px; font-weight: 600; text-align: center; color: var(--t-ink); background: #FAFBFD; transition: border-color .2s, box-shadow .2s, background .2s; }
    .t-input:hover { border-color: #CDD3E2; }
    .t-input:focus, select.sel-answer:focus, textarea:focus { outline: none; border-color: var(--t-tone); background: #fff; box-shadow: 0 0 0 4px var(--t-soft); }
    .t-line { margin: 0 0 12px; }

    /* Multiple choice */
    .mc-tab .mc-q { margin: 0 0 20px; }
    .mc-tab .mc-stem { display: flex; gap: 10px; align-items: flex-start; margin-bottom: 8px; font-weight: 500; }
    .mc-tab .qn, .q-row .qn, .mc-q .qn { background: var(--hp-navy); color: #fff; border: 0; border-radius: 7px; font-weight: 700; font-size: 12px; padding: 0 7px; line-height: 22px; min-width: 24px; text-align: center; flex: none; }
    .mc-tab label { display: flex; align-items: center; gap: 12px; padding: 8px 12px; margin: 0 0 4px; border-radius: 12px; border: 1.5px solid transparent; cursor: pointer; font-weight: 400; transition: background .15s, border-color .15s; }
    .mc-tab label:hover { background: var(--t-bg); }
    .mc-tab label:has(input:checked) { background: var(--t-soft); border-color: var(--t-tone); font-weight: 600; }
    .mc-tab input[type=radio], .mc-tab input[type=checkbox] { accent-color: var(--t-tone); margin: 0; width: 17px; height: 17px; }

    /* Class sheets: statement rows, option boxes, tables, figures */
    .p-intro { font-style: italic; color: var(--t-muted); }
    .q-row { display: flex; align-items: flex-start; gap: 12px; margin: 0 0 12px; }
    .q-row .qn, .mc-q .qn { margin-top: 3px; }
    .q-row .q-text { flex: 1; }
    .q-row .lp { flex: none; }
    .opt-box { border: 1px solid var(--t-line); border-radius: 14px; padding: 14px 18px; margin: 0 0 16px; background: linear-gradient(180deg, #FAFBFD, #F5F6FA); }
    .opt-title { font-weight: 800; color: var(--hp-navy); margin-bottom: 8px; font-size: 14px; }
    .opt-box ul { list-style: none; margin: 0; padding: 0; }
    .opt-box li { padding: 3px 0; }
    .opt-box li b { display: inline-block; min-width: 34px; color: var(--t-tone); font-weight: 800; }
    .opt-box.wide ul { display: grid; grid-template-columns: repeat(auto-fill, minmax(170px, 1fr)); gap: 4px 20px; }
    .opt-box.wide li { padding: 5px 0; }
    .q-table-wrap { overflow-x: auto; margin: 0 0 16px; border: 1px solid var(--t-line); border-radius: 14px; }
    .q-table { border-collapse: collapse; width: 100%; font-size: 14px; }
    .q-table th, .q-table td { border-bottom: 1px solid var(--t-line); border-right: 1px solid var(--t-line); padding: 10px 12px; text-align: left; vertical-align: top; }
    .q-table tr > :last-child { border-right: 0; }
    .q-table tr:last-child > * { border-bottom: 0; }
    .q-table th { background: var(--t-soft); color: var(--hp-navy); font-weight: 700; }
    .q-table .t-input { width: 150px; min-width: 150px; }
    .q-figure { margin: 0 0 16px; border: 1px solid var(--t-line); border-radius: 14px; padding: 14px; background: #fff; position: relative; overflow: auto; }
    .t-line .t-input { margin: 2px 0; }

    /* Drag & drop matching */
    .dnd-match { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .dnd-col-title { font-size: 11px; font-weight: 800; letter-spacing: 1.8px; color: var(--t-muted); margin-bottom: 12px; text-transform: uppercase; }
    .dnd-row { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
    .dnd-row > span { min-width: 70px; }
    .dnd-box { min-width: 200px; min-height: 38px; border: 1.5px dashed #CDD3E2; border-radius: 10px; padding: 4px 12px; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 13px; color: var(--t-muted); background: #FAFBFD; transition: border-color .15s, background .15s, transform .15s; }
    .dnd-box.filled { border-style: solid; border-color: var(--t-tone); color: var(--t-ink); background: var(--t-soft); font-weight: 600; cursor: move; }
    .dnd-box.hover { border-color: var(--t-tone); background: var(--t-soft); transform: scale(1.02); }
    .dnd-chip { display: block; width: fit-content; background: #fff; border: 1px solid var(--t-line); border-radius: 10px; padding: 7px 14px; margin-bottom: 8px; font-size: 13px; font-weight: 600; cursor: move; user-select: none; touch-action: none; box-shadow: var(--shadow-sm); transition: transform .15s, box-shadow .15s, opacity .2s; }
    .dnd-chip:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(10,22,64,.1); }
    .dnd-chip.used { opacity: .3; }
    .dnd-ghost { position: fixed; z-index: 200; pointer-events: none; box-shadow: 0 16px 32px rgba(10,22,64,.25); transform: rotate(-2deg) scale(1.04); border-color: var(--t-tone); }
    .dnd-ghost.glide { transition: left .18s ease, top .18s ease, opacity .18s; }

    /* Writing prompt */
    .w-prompt { font-weight: 600; margin: 0 0 12px; }
    .w-instr { font-style: italic; background: var(--t-bg); border-left: 3px solid var(--t-tone); border-radius: 0 12px 12px 0; padding: 12px 16px; color: var(--t-muted); margin: 0 0 18px; font-size: 14px; }
    .w-chart { border: 1px solid var(--t-line); border-radius: 14px; padding: 16px; background: #fff; }
    .w-area { width: 100%; height: calc(100% - 34px); min-height: 260px; resize: none; border: 1.5px solid var(--t-line); border-radius: 14px; padding: 18px; font-size: 15.5px; line-height: 1.8; color: var(--t-ink); background: #FAFBFD; transition: border-color .2s, box-shadow .2s, background .2s; }
    .w-count { font-size: 12px; font-weight: 700; letter-spacing: .5px; color: var(--t-muted); text-align: right; margin-top: 8px; }
    .t-hidden-count { display: none; }

    /* Bottom tab bar: names only */
    .t-tabbar { position: fixed; left: 0; right: 0; bottom: 0; height: var(--t-bar); background: rgba(255,255,255,.85); backdrop-filter: saturate(180%) blur(16px); -webkit-backdrop-filter: saturate(180%) blur(16px); border-top: 1px solid var(--t-line); display: flex; align-items: center; gap: 8px; padding: 0 20px; }
    .t-tablist { display: flex; gap: 4px; overflow-x: auto; flex: 0 1 auto; min-width: 0; scrollbar-width: none; background: var(--t-bg); padding: 4px; border-radius: 14px; }
    .t-tab { border: 0; background: transparent; color: var(--t-muted); padding: 8px 18px; border-radius: 10px; font-weight: 600; font-size: 14px; white-space: nowrap; transition: background .2s, color .2s, box-shadow .2s; }
    .t-tab:hover { color: var(--hp-navy); }
    .t-tab.active { background: #fff; color: var(--t-tone); font-weight: 700; box-shadow: 0 1px 3px rgba(10,22,64,.1), 0 4px 10px rgba(10,22,64,.06); }
    .t-arrow { width: 42px; height: 42px; border-radius: 50%; border: 0; background: var(--hp-navy); color: #fff; display: grid; place-items: center; flex: none; transition: transform .2s var(--ease), box-shadow .2s, opacity .2s; box-shadow: 0 6px 16px -6px rgba(10,22,64,.5); }
    .t-arrow.prev { margin-left: auto; }
    .t-tab.locked { opacity: .45; cursor: not-allowed; }
    .t-tab.locked::after { content: 'lock'; font-family: 'Material Icons Outlined'; font-size: 13px; margin-left: 6px; vertical-align: -2px; }
    .t-next-ex { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 999px; border: 0; background: var(--t-tone); color: #fff; font-weight: 700; font-size: 14px; cursor: pointer; box-shadow: 0 6px 16px -8px rgba(10,22,64,.5); white-space: nowrap; }
    .t-next-ex .material-icons-outlined { font-size: 18px; }
    .t-next-ex:not([hidden]) ~ .t-arrow.prev { margin-left: 8px; }
    .t-tablist.hw { margin-left: auto; background: #FFF7E6; align-items: center; }
    .t-tablist.hw[hidden], .t-next-ex[hidden] { display: none; }
    .t-tablist.hw:not([hidden]) ~ .t-arrow.prev { margin-left: 8px; }
    .t-tablist.hw .hw-label { display: inline-flex; align-items: center; gap: 5px; padding: 0 10px 0 8px; font-size: 12px; font-weight: 800; letter-spacing: .4px; text-transform: uppercase; color: #B45309; white-space: nowrap; }
    .t-tablist.hw .hw-label .material-icons-outlined { font-size: 17px; }
    .t-tablist.hw .t-tab.active { color: #B45309; }
    /* motivation popup after a homework hand-in */
    .t-moti { position: fixed; inset: 0; z-index: 400; display: none; align-items: center; justify-content: center; padding: 16px; background: rgba(10,22,64,.45); backdrop-filter: blur(3px); }
    .t-moti.open { display: flex; animation: moFade .25s ease; }
    .t-moti .mo-card { width: min(440px, 100%); background: #fff; border-radius: 22px; padding: 26px 26px 22px; text-align: center; box-shadow: 0 30px 60px -20px rgba(10,22,64,.45); border-top: 6px solid var(--t-tone); animation: moPop .35s cubic-bezier(.2,1.4,.4,1); }
    .t-moti .mo-logo { display: flex; justify-content: center; margin: 0 0 8px; }
    .t-moti .mo-emoji { font-size: 44px; line-height: 1.2; }
    .t-moti .mo-title { margin: 6px 0 8px; font-size: 21px; color: #0B1B3F; }
    .t-moti .mo-text { margin: 0 0 12px; color: #374151; font-size: 15px; line-height: 1.6; }
    .t-moti .mo-from { font-size: 13px; font-weight: 700; color: var(--t-tone); margin-bottom: 16px; }
    .t-moti .mo-ok { min-width: 160px; }
    /* thin bar that empties while the popup counts down to closing by itself */
    .t-moti .mo-card { position: relative; overflow: hidden; }
    .t-moti .mo-card::after { content: ''; position: absolute; left: 0; bottom: 0; height: 4px; width: 100%; background: var(--t-tone); opacity: .5; transform-origin: left; }
    .t-moti.open .mo-card::after { animation: moTime 5s linear forwards; }
    @keyframes moTime { from { transform: scaleX(1); } to { transform: scaleX(0); } }
    @keyframes moFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes moPop { from { transform: scale(.85); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    /* question numbers in the bottom bar: range when closed, one box per question when open (filled = answered) */
    .t-tab { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; }
    .t-tab .tr { font-weight: 600; color: var(--t-muted); }
    .t-tab .tr:empty, .t-tab.active .tr { display: none; }
    .t-tab .tq { display: none; gap: 4px; }
    .t-tab.active .tq { display: inline-flex; }
    .t-tab .qn { min-width: 26px; height: 26px; padding: 0 5px; border: 1.5px solid #CFD6E2; border-radius: 6px; background: #fff; color: var(--t-tone); font: 700 12px/1 inherit; font-family: inherit; cursor: pointer; transition: background .15s, border-color .15s, color .15s; }
    .t-tab .qn:hover { border-color: var(--t-tone); }
    .t-tab .qn.filled { background: var(--t-tone) !important; border-color: var(--t-tone) !important; color: #fff !important; }
    .t-tablist.hw .t-tab .qn { color: #B45309; }
    .t-tablist.hw .t-tab .qn.filled { background: #D97706 !important; border-color: #D97706 !important; color: #fff !important; }
    .t-arrow:not(:disabled):hover { transform: translateY(-2px); box-shadow: 0 10px 22px -6px rgba(10,22,64,.6); }
    .t-arrow:disabled { opacity: .25; cursor: default; box-shadow: none; }

    /* Note popup */
    mark.hl { background: linear-gradient(180deg, transparent 15%, #FFE68A 15%); border-radius: 3px; padding: 0 1px; cursor: pointer; }
    mark.hl { color: inherit; }
    mark.hl.has-note { background: linear-gradient(180deg, transparent 15%, #FFD966 15%); box-shadow: inset 0 -2px 0 var(--hp-gold); }
    mark.hl.has-note.head::after { content: 'sticky_note_2'; font-family: 'Material Icons Outlined'; font-size: 13px; color: #B45309; margin-left: 2px; vertical-align: 1px; }
    .t-ctx { position: fixed; z-index: 160; display: none; min-width: 168px; background: #fff; border: 1px solid var(--t-line); border-radius: 14px; box-shadow: var(--shadow-lg); padding: 6px; animation: pop .15s var(--ease); }
    .t-ctx.open { display: block; }
    .t-ctx button { display: flex; align-items: center; gap: 10px; width: 100%; border: 0; background: none; border-radius: 9px; padding: 8px 10px; font-size: 14px; font-weight: 600; color: var(--hp-navy); text-align: left; cursor: pointer; }
    .t-ctx button:hover, .t-ctx button:focus-visible { background: var(--t-bg); outline: 0; }
    .t-ctx button .material-icons-outlined { font-size: 19px; color: var(--t-muted); }
    .t-ctx [data-act="hl"] .material-icons-outlined { color: #D4A017; }
    .t-ctx [data-act="note"] .material-icons-outlined { color: #B45309; }
    .t-ctx [data-act="all"], .t-ctx [data-act="all"] .material-icons-outlined { color: var(--hp-red); }
    .t-ctx hr { border: 0; border-top: 1px solid var(--t-line); margin: 4px 2px; }
    .note-popup { position: fixed; z-index: 150; width: 320px; background: #fff; border-radius: 18px; border: 1px solid var(--t-line); box-shadow: var(--shadow-lg); padding: 16px; display: none; animation: pop .2s var(--ease); }
    .note-popup::before { content: ''; position: absolute; left: 16px; right: 16px; top: 0; height: 3px; border-radius: 0 0 3px 3px; background: linear-gradient(90deg, var(--hp-gold-2), var(--hp-gold)); }
    .note-popup.open { display: block; }
    @keyframes pop { from { opacity: 0; transform: scale(.94) translateY(4px); } }
    .note-popup .sel { background: #FFF6DE; border-left: 3px solid var(--hp-gold); border-radius: 4px 10px 10px 4px; padding: 8px 34px 8px 10px; font-weight: 600; font-size: 14px; margin-bottom: 12px; }
    .note-popup .x { position: absolute; right: 12px; top: 14px; border: 0; background: none; color: var(--t-muted); width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; }
    .note-popup .x:hover { background: var(--t-bg); }
    .note-popup textarea { width: 100%; height: 96px; resize: vertical; background: var(--t-bg); border: 1.5px solid var(--t-line); border-radius: 12px; padding: 10px 12px; font-size: 14px; }
    .note-popup .acts { display: flex; justify-content: space-between; margin-top: 12px; }
    .note-popup .acts button { border: 0; border-radius: 10px; padding: 8px 16px; font-size: 13px; font-weight: 700; }
    .note-popup .del { background: #FDE9EC; color: var(--hp-red); }
    .note-popup .save { background: var(--hp-navy); color: #fff; }

    /* Finish overlay */
    .t-done { position: fixed; inset: 0; background: rgba(10,22,64,.5); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: none; place-items: center; z-index: 300; }
    .t-done.open { display: grid; animation: fade .3s; }
    .t-done .card { position: relative; overflow: hidden; background: #fff; border-radius: 24px; padding: 40px 32px 32px; width: min(440px, calc(100% - 32px)); text-align: center; box-shadow: 0 40px 80px -20px rgba(0,0,0,.4); animation: pop .35s var(--ease); }
    .t-done .card::before { content: 'workspace_premium'; font-family: 'Material Icons Outlined' !important; display: grid; place-items: center; width: 68px; height: 68px; margin: 0 auto 18px; border-radius: 20px; background: linear-gradient(180deg, var(--hp-gold-2), var(--hp-gold)); color: var(--hp-navy); font-size: 36px; box-shadow: 0 14px 30px -10px rgba(232,184,74,.8); }
    .t-done h3 { margin: 0 0 8px; color: var(--hp-navy); font-weight: 800; font-size: 22px; letter-spacing: -.02em; }
    .t-done p { color: var(--t-muted); margin: 0 0 24px; }
    .t-done.busy .card::before { content: 'cloud_upload'; animation: pulse 1.2s ease-in-out infinite; }
    @keyframes pulse { 50% { transform: scale(.92); opacity: .75; } }

    /* Result popup: summary cards on top, then every answer next to the correct one */
    .t-res { position: fixed; inset: 0; background: rgba(10,22,64,.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); display: none; place-items: center; z-index: 320; padding: 16px; }
    .t-res.open { display: grid; animation: fade .25s; }
    .res-card { position: relative; background: #fff; border-radius: 22px; width: min(860px, 100%); max-height: calc(100vh - 32px); display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 40px 90px -24px rgba(10,22,64,.55); animation: pop .35s var(--ease); }
    .res-head { position: relative; padding: 26px 28px 22px; background: linear-gradient(135deg, #0B1B3F 0%, #1D3170 100%); color: #fff; }
    .res-head h3 { margin: 0; font-size: 20px; font-weight: 800; letter-spacing: -.01em; }
    .res-head .res-sub { margin-top: 4px; font-size: 13px; color: rgba(255,255,255,.7); }
    .res-x { position: absolute; top: 18px; right: 18px; width: 36px; height: 36px; display: grid; place-items: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.12); color: #fff; cursor: pointer; transition: background .15s; }
    .res-x:hover { background: rgba(255,255,255,.24); }
    .res-x .material-icons-outlined { font-size: 20px; }
    .res-stats { display: grid; grid-template-columns: repeat(5, 1fr); gap: 10px; margin-top: 20px; }
    .res-stat { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); border-radius: 14px; padding: 12px 10px; text-align: center; }
    .res-stat b { display: block; font-size: 22px; font-weight: 800; line-height: 1.1; }
    .res-stat small { display: block; margin-top: 4px; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: rgba(255,255,255,.65); }
    .res-stat.band { background: linear-gradient(180deg, var(--hp-gold-2, #F6D27A), var(--hp-gold, #E8B84A)); border-color: transparent; color: #0B1B3F; }
    .res-stat.band small { color: rgba(11,27,63,.7); }
    .res-stat.ok b { color: #6EE7A0; } .res-stat.bad b { color: #FCA5A5; }
    .res-bar { height: 8px; margin-top: 18px; border-radius: 99px; background: rgba(255,255,255,.14); overflow: hidden; }
    .res-bar i { display: block; height: 100%; width: 0; border-radius: 99px; background: linear-gradient(90deg, #22C55E, #6EE7A0); transition: width .8s var(--ease); }
    .res-body { overflow: auto; padding: 8px 20px 20px; }
    .res-table { width: 100%; border-collapse: separate; border-spacing: 0 8px; font-size: 14.5px; }
    .res-table th { position: sticky; top: 0; z-index: 1; background: #fff; padding: 12px 14px 6px; text-align: left; font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #8792A6; }
    .res-table td { padding: 12px 14px; color: #0B1B3F; background: #F7F8FB; vertical-align: middle; }
    .res-table td:first-child { width: 56px; border-radius: 12px 0 0 12px; border-left: 4px solid #DC2626; }
    .res-table td:last-child { width: 44px; border-radius: 0 12px 12px 0; text-align: center; }
    .res-table tr.ok td:first-child { border-left-color: #16A34A; }
    .res-table .qno { display: inline-grid; place-items: center; min-width: 28px; height: 28px; padding: 0 6px; border-radius: 8px; background: #0B1B3F; color: #fff; font-size: 13px; font-weight: 700; }
    .res-table .given { font-weight: 600; }
    .res-table tr.bad .given { color: #B91C1C; }
    .res-table tr.ok .given { color: #15803D; }
    .res-table .empty { color: #9AA4B8; font-style: italic; font-weight: 500; }
    .res-table .correct { font-weight: 600; color: #15803D; }
    .res-table .st { display: inline-grid; place-items: center; width: 28px; height: 28px; border-radius: 50%; font-size: 18px; }
    .res-table tr.ok .st { background: #DCFCE7; color: #16A34A; }
    .res-table tr.bad .st { background: #FEE2E2; color: #DC2626; }
    .res-foot { display: flex; justify-content: flex-end; padding: 14px 20px; border-top: 1px solid #EEF1F6; }
    @media (max-width: 640px) {
        .res-stats { grid-template-columns: repeat(3, 1fr); }
        .res-stat.time, .res-stat.score { grid-column: span 1; }
        .res-head { padding: 22px 18px 18px; } .res-body { padding: 4px 10px 14px; }
        .res-table td, .res-table th { padding-left: 8px; padding-right: 8px; }
    }

    /* "Are you sure?" modal before finishing */
    .t-confirm { position: fixed; inset: 0; background: rgba(10,22,64,.5); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); display: none; place-items: center; z-index: 310; }
    .t-confirm.open { display: grid; animation: fade .2s; }
    .t-confirm .box { background: #fff; border-radius: 20px; width: min(460px, calc(100% - 32px)); box-shadow: 0 40px 80px -20px rgba(0,0,0,.4); animation: pop .3s var(--ease); overflow: hidden; }
    .t-confirm .hd { display: flex; gap: 14px; align-items: flex-start; padding: 24px 24px 8px; }
    .t-confirm .hd .ic { flex: none; width: 46px; height: 46px; border-radius: 14px; display: grid; place-items: center; background: var(--t-soft); color: var(--t-tone); }
    .t-confirm h3 { margin: 2px 0 6px; font-size: 18px; font-weight: 800; color: var(--hp-navy); letter-spacing: -.01em; }
    .t-confirm p { margin: 0; color: var(--t-muted); font-size: 14.5px; line-height: 1.5; }
    .t-confirm .cnt { margin: 14px 24px 0; padding: 10px 14px; border-radius: 12px; background: var(--t-bg); font-size: 14px; color: var(--hp-navy); }
    .t-confirm .cnt b { font-weight: 800; }
    .t-confirm .cnt.warn { background: #FFF7E6; color: #92400E; }
    .t-confirm .ft { display: flex; justify-content: flex-end; gap: 10px; padding: 20px 24px 22px; }
    .t-confirm .ft button { border: 0; border-radius: 999px; padding: 10px 20px; font-weight: 700; font-size: 14px; cursor: pointer; }
    .t-confirm .back { background: var(--t-bg); color: var(--hp-navy); }
    .t-confirm .go { background: var(--t-tone); color: #fff; box-shadow: 0 8px 18px -10px var(--t-tone); }
    .t-confirm .go:hover { filter: brightness(1.08); }

    @media (max-width: 860px) {
        body { overflow: auto; }
        .t-tabs { position: static; padding-bottom: calc(var(--t-bar) + 14px); }
        .t-pane.active { display: flex; flex-direction: column; height: auto; }
        .t-passage, .t-questions { overflow: visible; padding: 20px; }
        .t-playing, .t-topbar .logo { display: none; }
        .t-timer { margin-inline: 0 auto; }
        .dnd-match { grid-template-columns: 1fr; }
    }

    /* Per-exercise time limit */
    .t-pane.timeup .t-questions { opacity: .55; pointer-events: none; }
    .t-pane .timeup-note { grid-column: 1 / -1; display: flex; align-items: center; gap: 8px; background: #FEF3E2; color: #92400E; border-radius: 12px; padding: 10px 14px; font-weight: 600; font-size: 14px; }
    .t-tab.done::after { content: 'lock'; font-family: 'Material Icons Outlined'; font-size: 14px; margin-left: 6px; vertical-align: -2px; opacity: .7; }

    .time-toast { position: fixed; top: 76px; left: 50%; transform: translateX(-50%); z-index: 300; display: flex; align-items: center; gap: 8px; background: var(--hp-navy); color: #fff; padding: 12px 18px; border-radius: 14px; font-weight: 600; font-size: 14px; box-shadow: 0 16px 40px -12px rgba(10,22,64,.5); transition: opacity .4s, transform .4s; }
    .time-toast .material-icons-outlined { color: var(--hp-gold-2); }
    .time-toast.out { opacity: 0; transform: translate(-50%, -10px); }

    /* Answers shown by the teacher */
    .ans-banner { display: flex; align-items: center; gap: 10px; margin: 0 auto 14px; width: min(1240px, calc(100% - 32px)); background: #E7F6EC; color: #15803D; border-radius: 14px; padding: 12px 16px; font-weight: 600; }
    /* Answer marks: number badge + round ✓ / ✗, the box, then the correct answer as a soft green pill */
    .ans-mark { display: inline-flex; align-items: center; gap: 5px; margin: 0 6px 0 2px; vertical-align: middle; }
    .ans-num { display: inline-grid; place-items: center; min-width: 26px; height: 26px; padding: 0 6px; border-radius: 7px; background: #DC2626; color: #fff; font-size: 12.5px; font-weight: 800; box-shadow: 0 4px 10px -4px rgba(220,38,38,.6); }
    .ans-tick { display: inline-grid; place-items: center; width: 20px; height: 20px; border-radius: 50%; background: #FEE2E2; color: #DC2626; font-size: 12px; font-weight: 900; line-height: 1; }
    .ans-mark.ok .ans-num { background: #16A34A; box-shadow: 0 4px 10px -4px rgba(22,163,74,.6); }
    .ans-mark.ok .ans-tick { background: #DCFCE7; color: #16A34A; }
    .ans-num, .ans-tick { transition: background .25s, color .25s, box-shadow .25s, transform .25s; }
    .ans-mark.ok .ans-tick { transform: scale(1.1); }
    .ans-group .t-input { transition: border-color .25s, background .25s, box-shadow .25s; }
    .ans-group:has(.ans-mark.ok) .t-input { border-color: #16A34A; background: #F0FDF4; box-shadow: 0 0 0 3px rgba(22,163,74,.12); }
    .ans-group:has(.ans-mark.bad) .t-input:not(:placeholder-shown) { border-color: #DC2626; background: #FEF2F2; box-shadow: 0 0 0 3px rgba(220,38,38,.10); }
    .ans-txt { display: inline-flex; align-items: center; gap: 5px; margin: 0 10px 0 10px; padding: 3px 11px 3px 8px; border-radius: 999px; background: #ECFDF3; border: 1px solid #BBF7D0; color: #166534; font-size: 13px; font-weight: 500; font-style: normal; vertical-align: middle; line-height: 1.5; }
    .ans-txt::before { content: 'check_circle'; font-family: 'Material Icons Outlined'; font-size: 15px; color: #16A34A; }
    .ans-txt b { font-weight: 700; color: #14532D; }
    .retest-bar { display: flex; align-items: center; justify-content: flex-end; gap: 10px; margin: 0 0 10px; }
    .retest-bar .rt-score { display: inline-flex; align-items: center; gap: 5px; font-size: 12.5px; color: #5E6A85; }
    .retest-bar .rt-score .material-icons-outlined { font-size: 16px; color: var(--t-tone); }
    .retest-bar .rt-score b { color: #0B1B3F; }
    .retest-btn { display: inline-flex; align-items: center; gap: 4px; padding: 4px 11px 4px 8px; border: 1px solid #D5DBE6; border-radius: 999px; background: #fff; color: #0B1B3F; font: inherit; font-size: 12.5px; font-weight: 700; cursor: pointer; transition: background .15s, color .15s, border-color .15s; }
    .retest-btn .material-icons-outlined { font-size: 16px; }
    .retest-btn:hover { background: #0B1B3F; border-color: #0B1B3F; color: #fff; }
    .ans-yours { display: inline-flex; align-items: center; gap: 5px; margin: 0 0 0 4px; padding: 3px 11px 3px 8px; border-radius: 999px; background: #FEF2F2; border: 1px solid #FECACA; color: #991B1B; font-size: 13px; font-weight: 500; vertical-align: middle; line-height: 1.5; }
    .ans-yours::before { content: 'cancel'; font-family: 'Material Icons Outlined'; font-size: 15px; color: #DC2626; }
    .ans-yours b { font-weight: 700; text-decoration: line-through; text-decoration-color: rgba(220,38,38,.6); }
    .mc-q label.is-wrong { background: #FEF2F2; border-radius: 8px; outline: 2px solid #DC2626; }
    .mc-q label .your-tag { margin-left: auto; padding: 2px 10px; border-radius: 999px; background: #DC2626; color: #fff; font-size: 12px; font-weight: 700; }
    .mc-q label .your-tag b { margin-left: 2px; }
    .ans-key { display: inline-block; margin: 0 6px; padding: 3px 10px; border-radius: 8px; background: #15803D; color: #fff; font-size: 13px; font-weight: 700; vertical-align: middle; }
    .t-input.ans-ok, select.sel-answer.ans-ok { border-color: #16A34A; background: #E7F6EC; }
    .t-input.ans-bad, select.sel-answer.ans-bad { border-color: #DC2626; background: #FDECEC; }
    /* a marked exercise stays fully readable (not faded like a time-up one) */
    .t-pane.marked .t-questions { opacity: 1; }
    .t-pane.marked .timeup-note { background: #E7F6EC; color: #15803D; }
    .timeup-note .nt { flex: 1; }
    .practice-btn { display: inline-flex; align-items: center; gap: 6px; flex: none; border: 1.5px solid currentColor; background: #fff; color: #0B1B3F; border-radius: 10px; padding: 6px 12px; font: inherit; font-size: 13px; font-weight: 700; cursor: pointer; transition: background .15s, color .15s; }
    .practice-btn:hover { background: #0B1B3F; color: #fff; border-color: #0B1B3F; }
    .practice-btn .material-icons-outlined { font-size: 17px; }
    .ans-group { display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px 6px; vertical-align: middle; margin: 2px 4px; max-width: 100%; }
    .ans-group .t-input { margin: 0; color: #0B1B3F; opacity: 1; -webkit-text-fill-color: #0B1B3F; }
    .ans-group .ans-mark { margin: 0; }
    .ans-group .ans-txt { margin: 0 0 0 4px; }
    .q-table td .ans-group { display: flex; margin: 6px 0; }
    .q-table td .ans-group .t-input { flex: 1 1 120px; min-width: 0; }
    .mc-q label.is-answer { background: #E7F6EC; border-radius: 8px; outline: 2px solid #16A34A; }
    .mc-q.ans-bad { border-left: 4px solid #DC2626; padding-left: 8px; }
    .mc-q.ans-ok { border-left: 4px solid #16A34A; padding-left: 8px; }

    /* Drag-to-resize divider between passage and questions */
    .t-pane:not(.single) { grid-template-columns: minmax(0, var(--split, 1fr)) 10px minmax(0, 1fr); column-gap: 8px; }
    .t-resizer { position: relative; z-index: 3; cursor: ew-resize; touch-action: none; user-select: none; background: #E5E7EB; border-radius: 2px; }
    .t-resizer::before { content: ''; position: absolute; top: 0; bottom: 0; left: 50%; width: 2px; transform: translateX(-50%); background: #111827; transition: background .15s; }
    .t-resizer::after { content: ''; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 28px; height: 28px; background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 12'%3E%3Cpath d='M5 6h10' stroke='%23111827' stroke-width='1.8'/%3E%3Cpath d='M0.5 6 6 1.5v9zM19.5 6 14 1.5v9z' fill='%23111827'/%3E%3C/svg%3E") center / 15px 9px no-repeat; border: 1.5px solid #111827; border-radius: 2px; pointer-events: none; }
    .t-resizer:hover, .t-resizer.dragging { background: #DBEAFE; }
    body.t-resizing, body.t-resizing * { cursor: ew-resize !important; user-select: none !important; }
    @media (max-width: 860px) { .t-resizer { display: none; } }

    /* ================= CD IELTS look =================
       White top bar (timer pill left, Finish + notes right), header card with a coloured left edge,
       passage in a white card, questions on the page background, full-width bottom tabs,
       round navy arrows floating above the tab bar. */
    :root { --t-ink: #0B1B3F; --t-muted: #5B6B86; --t-line: #E4E9F2; --t-bg: #F7F9FD; --t-top: 64px; --t-bar: 62px; }
    body { background: radial-gradient(900px 500px at 100% 0%, var(--t-soft) 0%, transparent 55%), var(--t-bg); }
    body, body *:not(.material-icons-outlined) { font-family: 'Poppins', 'Hind Siliguri', system-ui, sans-serif !important; }

    .t-topbar { background: rgba(255,255,255,.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); color: var(--t-ink); border-bottom: 1px solid var(--t-line); box-shadow: 0 6px 20px -16px rgba(11,27,63,.35); padding: 0 20px; z-index: 50; }
    .t-topbar::after { display: none; }
    .t-topbar .logo { order: 0; background: none; box-shadow: none; padding: 0; }
    .t-topbar .logo img { display: block; height: 46px; width: auto; }
    .t-timer { order: 1; margin: 0 auto 0 4px; background: var(--t-soft); color: var(--t-tone); border: 0; padding: 7px 16px; font-weight: 800; font-size: 15px; letter-spacing: 0; }
    .t-timer .material-icons-outlined { display: none; }
    .t-timer .v::after { content: ' remaining'; font-weight: 800; }
    .t-timer[hidden] { display: none; }
    .t-timer[hidden] ~ .t-finish { margin-left: auto; }
    .t-timer.low { background: #FDECEE; color: #C81E33; border: 0; }
    .t-playing { order: 2; color: var(--t-muted); }
    .t-finish { order: 3; }
    .t-notes { order: 4; }
    .t-btn.primary { background: #0B1B3F; color: #fff; box-shadow: none; border-radius: 999px; padding: 9px 20px; font-weight: 700; }
    .t-btn.primary:hover { background: var(--t-tone); box-shadow: none; transform: translateY(-1px); }
    .t-notes > .t-btn.light { width: 38px; height: 38px; padding: 0; justify-content: center; border-radius: 50%; background: #fff; border: 1px solid var(--t-line); color: var(--t-tone); position: relative; }
    .t-notes > .t-btn.light:hover { border-color: var(--t-tone); background: #fff; }
    .t-notes > .t-btn.light .material-icons-outlined { font-size: 20px; }
    .t-notes > .t-btn.light .c { position: absolute; top: -5px; right: -5px; min-width: 18px; height: 18px; font-size: 10.5px; background: var(--t-tone); color: #fff; }

    .t-head { margin: 20px 48px 0; padding: 16px 24px 16px 28px; border-radius: 18px; position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) auto; column-gap: 16px; align-items: center; box-shadow: 0 10px 30px -24px rgba(11,27,63,.35); }
    .t-head::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 5px; background: var(--t-tone); }
    .t-head .dot { display: none; }
    .t-head strong { grid-column: 1; font-size: 22px; font-weight: 800; letter-spacing: -.3px; line-height: 1.3; color: var(--t-ink); }
    .t-head span:not(.dot):not(.material-icons-outlined) { grid-column: 1; padding: 0; border: 0; font-weight: 600; font-size: 14.5px; color: var(--t-muted); }
    .t-head .t-hw { grid-column: 2; grid-row: 1 / span 2; margin: 0; }
    .t-head .t-hw span { color: inherit !important; font-size: inherit !important; }
    .t-hw-note { margin: 10px 48px 0; }

    .t-tabs { top: calc(var(--t-top) + 108px); padding: 18px 48px 16px; }
    .t-passage { border-radius: 18px; padding: 22px 24px; box-shadow: 0 10px 30px -26px rgba(11,27,63,.35); line-height: 1.75; }
    .t-questions { background: transparent; border: 0; box-shadow: none; border-radius: 0; padding: 4px 8px 80px 14px; }
    .t-pane.single .t-passage { max-width: 1100px; }
    .t-passage h3 { text-align: center; font-size: 20px; color: var(--t-ink); }
    .t-passage p { line-height: 1.75; }
    .t-questions h4 { font-size: 24px; font-weight: 700; color: var(--t-ink); letter-spacing: -.3px; margin: 34px 0 10px; padding-top: 0; border-top: 0; }
    .t-questions h4:first-child { margin-top: 0; }
    .t-instr { color: var(--t-ink); font-size: 15px; }
    .topic-title { color: #0B1B3F; font-size: 18px !important; font-weight: 700; }

    .t-input, select.sel-answer { border: 1px solid #CFD6E2; border-radius: 8px; background: #fff; text-align: left; font-weight: 600; }
    .t-input:hover, select.sel-answer:hover { border-color: #AAB4C5; }
    .t-input::placeholder { color: #9AA4B5; font-weight: 700; }
    select.sel-answer { width: auto; min-width: 68px; }
    .mc-tab .qn, .q-row .qn, .mc-q .qn { background: #0B1B3F; border-radius: 6px; }
    .opt-box, .q-table-wrap, .q-figure { background: #fff; }

    .t-tabbar { background: rgba(255,255,255,.92); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 -10px 30px -24px rgba(11,27,63,.4); padding: 0 14px; }
    .t-tablist:not(.hw) { flex: 1 1 auto; justify-content: space-between; background: transparent; padding: 0; gap: 6px; }
    .t-tab { padding: 10px 16px; border-radius: 12px; font-weight: 700; color: var(--t-muted); }
    .t-tab:hover { background: var(--t-bg); color: var(--t-ink); }
    .t-tab.active { background: var(--t-soft); color: var(--t-tone); box-shadow: none; }
    .t-tablist.hw { flex: none; }
    .t-arrow { position: fixed; bottom: calc(var(--t-bar) - 18px); width: 48px; height: 48px; z-index: 40; background: #0B1B3F; box-shadow: 0 10px 22px -8px rgba(11,27,63,.55); }
    .t-arrow.prev { right: 80px; margin: 0; }
    .t-arrow.next { right: 20px; }
    .t-next-ex:not([hidden]) ~ .t-arrow.prev, .t-tablist.hw:not([hidden]) ~ .t-arrow.prev { margin-left: 0; }
    .t-tabbar { padding-right: 150px; }   /* room for the floating arrows */
    .t-next-ex { margin-left: 12px; }


    /* ===== CD IELTS question types ===== */
    /* question cards: TRUE/FALSE/NOT GIVEN, YES/NO/NOT GIVEN and multiple choice */
    .mc-tab .mc-q { background: #fff; border: 1px solid var(--t-line); border-radius: 14px; padding: 16px 18px 8px; margin: 0 0 16px; box-shadow: 0 1px 2px rgba(11,27,63,.03); }
    .mc-tab .mc-stem { gap: 10px; margin-bottom: 6px; font-weight: 500; font-size: 15.5px; line-height: 1.6; }
    .mc-tab .qn, .q-row .qn, .mc-q .qn { background: #fff; color: #0B1B3F; border: 1.5px solid var(--t-tone); border-radius: 5px; font-size: 13px; font-weight: 800; min-width: 28px; line-height: 22px; padding: 0 5px; }
    .mc-tab label { gap: 14px; padding: 10px 14px; margin: 2px 0; border-radius: 10px; border: 1.5px solid transparent; font-weight: 500; font-size: 15px; }
    .mc-tab label:hover { background: #F5F7FB; }
    .mc-tab label:has(input:checked) { background: var(--t-soft); border-color: transparent; font-weight: 600; }
    .mc-tab input[type=radio], .mc-tab input[type=checkbox] { width: 18px; height: 18px; flex: none; }
    .tf-q label { font-weight: 700; letter-spacing: .2px; }
    .mc-tab .opt-l { font-weight: 800; min-width: 14px; color: #0B1B3F; }
    .mc-tab label:has(input:checked) .opt-l { color: var(--t-tone); }
    .mc-q.ans-ok { border-color: #16A34A; } .mc-q.ans-bad { border-color: #DC2626; }

    /* statement + dropdown rows (which paragraph, matching letters) */
    .q-row { align-items: center; gap: 10px; margin: 0 0 14px; }
    .q-row .q-text { flex: 0 1 auto; }
    select.sel-answer { height: 32px; padding: 2px 30px 2px 12px; font-weight: 700; color: #0B1B3F; }

    /* gap fill: number shown in the middle of the box */
    .t-input { width: 180px; min-width: 180px; height: 32px; text-align: center; font-weight: 600; }
    .t-input::placeholder { text-align: center; color: #9AA4B5; font-weight: 700; }
    .t-line { line-height: 2.3; }
    .q-table .t-input { width: 150px; min-width: 150px; }

    /* drag & drop matching */
    .dnd-col-title { font-size: 15px; font-weight: 700; letter-spacing: 0; text-transform: none; color: #0B1B3F; }
    .dnd-box { min-width: 200px; min-height: 44px; border: 1px solid #CFD6E2; border-style: solid; border-radius: 6px; background: #fff; color: #5B6B86; font-size: 14px; }
    .dnd-box.filled { background: #fff; border-color: var(--t-tone); color: #0B1B3F; font-weight: 700; }
    .dnd-chip { width: 100%; max-width: 240px; text-align: center; padding: 11px 14px; border: 0; border-radius: 6px; font-size: 14.5px; font-weight: 700; color: #0B1B3F; box-shadow: 0 2px 10px rgba(11,27,63,.12); margin-bottom: 12px; }
    .dnd-chip.used { opacity: .35; }
    @media (max-width: 860px) {
        .t-head { margin: 14px 16px 0; }
        .t-hw-note { margin: 10px 16px 0; }
        .t-tabs { padding: 14px 16px; }
        .t-questions { padding: 4px 0 90px; }
        .t-timer { font-size: 13.5px; padding: 6px 12px; }
        .t-arrow { width: 42px; height: 42px; bottom: calc(var(--t-bar) + 10px); }
        .t-arrow.prev { right: 70px; }
        .t-arrow.next { right: 16px; }
        .t-tabbar { padding-right: 14px; }
        .t-tablist:not(.hw) { justify-content: flex-start; }
    }
</style>

<header class="t-topbar">
    <a href="{{ route('home') }}" class="logo">@include('partials.hexas-logo', ['size' => 34])</a>
    <div class="t-timer" @if ($noTimer) hidden @endif data-minutes="{{ $minutes }}"><span class="material-icons-outlined">timer</span><span class="v">{{ $minutes }}:00</span></div>
    @if ($audio)
        <div class="t-playing"><span class="material-icons-outlined">graphic_eq</span>Audio is playing</div>
    @endif
    <div class="t-notes">
        <button type="button" class="t-btn light"><span class="material-icons-outlined">sticky_note_2</span><span class="c">0</span></button>
        <div class="t-notes-list"><div class="empty">Select text, then choose Notes to add one.</div></div>
    </div>
    <button type="button" class="t-btn primary t-finish">Finish test</button>
</header>

<div class="note-popup" role="dialog" aria-label="Note">
    <button type="button" class="x" aria-label="Close"><span class="material-icons-outlined">close</span></button>
    <div class="sel"></div>
    <textarea placeholder="Add your note here..."></textarea>
    <div class="acts"><button type="button" class="del">Remove</button><button type="button" class="save">Save</button></div>
</div>

<div class="t-ctx" role="menu" aria-label="Highlight and notes">
    <button type="button" role="menuitem" data-act="hl"><span class="material-icons-outlined">border_color</span>Highlight</button>
    <button type="button" role="menuitem" data-act="note"><span class="material-icons-outlined">sticky_note_2</span>Notes</button>
    <button type="button" role="menuitem" data-act="clear"><span class="material-icons-outlined">format_clear</span>Clear</button>
    <hr>
    <button type="button" role="menuitem" data-act="all"><span class="material-icons-outlined">delete_sweep</span>Clear all</button>
</div>

<div class="t-confirm" role="dialog" aria-modal="true" aria-labelledby="tConfirmTitle"><div class="box">
    <div class="hd"><span class="ic"><span class="material-icons-outlined">task_alt</span></span>
        <div><h3 id="tConfirmTitle">Are you sure you want to complete the test?</h3>
            <p>Once you continue, your answers will be submitted and you'll be taken back to the {{ $module }} page.</p></div></div>
    <div class="cnt"></div>
    <div class="ft"><button type="button" class="back">Go back</button><button type="button" class="go">Continue</button></div>
</div></div>

<div class="t-done"><div class="card">
    <h3>Test finished</h3>
    <p class="msg"></p>
    <a href="{{ $backUrl ?? route(strtolower($module)) }}" class="t-btn tone t-back" style="text-decoration:none">Back to {{ $module }}</a>
</div></div>

{{-- A few words of encouragement from Hexa's Zindabazar whenever anything is finished / handed in:
     class exercise, full test, homework, practice, writing or listening --}}
@if (true)
<div class="t-moti" role="dialog" aria-modal="true" aria-label="A message from Hexa's Zindabazar"><div class="mo-card">
    <div class="mo-logo">@include('partials.hexas-logo', ['size' => 30])</div>
    <div class="mo-emoji" aria-hidden="true"></div>
    <h3 class="mo-title"></h3>
    <p class="mo-text"></p>
    <div class="mo-from">— Team Hexa's Zindabazar</div>
    <button type="button" class="t-btn primary mo-ok">Thank you!</button>
</div></div>
@endif

{{-- Result popup shown right after a reading test is submitted --}}
<div class="t-res" role="dialog" aria-modal="true" aria-label="Your result"><div class="res-card">
    <div class="res-head">
        <button type="button" class="res-x" title="Close" aria-label="Close"><span class="material-icons-outlined">close</span></button>
        <h3 class="res-title">Your result</h3>
        <div class="res-sub"></div>
        <div class="res-stats">
            <div class="res-stat band"><b class="v-band">—</b><small>Band</small></div>
            <div class="res-stat score"><b class="v-score">0/0</b><small>Score</small></div>
            <div class="res-stat ok"><b class="v-ok">0</b><small>Correct</small></div>
            <div class="res-stat bad"><b class="v-bad">0</b><small>Wrong</small></div>
            <div class="res-stat time"><b class="v-time">0:00</b><small>Time</small></div>
        </div>
        <div class="res-bar"><i></i></div>
    </div>
    <div class="res-body"><table class="res-table">
        <thead><tr><th>#</th><th>Your answer</th><th>Correct answer</th><th></th></tr></thead>
        <tbody></tbody>
    </table></div>
    <div class="res-foot"><button type="button" class="t-btn primary res-done">See answers on the page</button></div>
</div></div>

<script>
/* The passage area starts just below the header card, however tall it is (a homework badge or a long
   class name makes the card taller, and a fixed offset would let the passage cover it). */
function tFitTabs() {
    var head = document.querySelector('.t-head'), tabs = document.querySelector('.t-tabs');
    if (!head || !tabs) return;
    tabs.style.top = '';
    if (getComputedStyle(tabs).position !== 'absolute') return;
    var need = head.getBoundingClientRect().bottom + window.scrollY - tabs.offsetParent.getBoundingClientRect().top - window.scrollY + 8;
    if (tabs.offsetTop < need) tabs.style.top = need + 'px';
}
document.addEventListener('DOMContentLoaded', tFitTabs);
window.addEventListener('load', tFitTabs);
window.addEventListener('resize', tFitTabs);
document.addEventListener('DOMContentLoaded', function () {
    /* ---------- Tabs: one passage per tab ---------- */
    var panes = [].slice.call(document.querySelectorAll('.t-pane'));
    var bar = document.createElement('nav');
    bar.className = 't-tabbar';
    bar.innerHTML = '<div class="t-tablist"></div><div class="t-tablist hw" hidden><span class="hw-label"><span class="material-icons-outlined">home_work</span>Homework</span></div>' +
        '<button type="button" class="t-arrow prev" aria-label="Previous"><span class="material-icons-outlined">arrow_back</span></button>' +
        '<button type="button" class="t-arrow next" aria-label="Next"><span class="material-icons-outlined">arrow_forward</span></button>';
    document.body.appendChild(bar);
    var list = bar.querySelector('.t-tablist'), hwList = bar.querySelector('.t-tablist.hw'), current = 0;
    var gate = null;   // set by the timer below: may this tab be opened yet?
    function tabEl(i) { return bar.querySelector('.t-tab[data-i="' + i + '"]'); }
    // Tabs named "Homework …" sit in their own group on the right side of the bar.
    // Each tab: its name, the range of its questions ("14–26"), and – while it is open – one small box per
    // question number (filled once answered, like CD IELTS). A box jumps to its question.
    var qnav = []; // per tab: [{ n: question number, name: field name, k: 1st / 2nd box of a choose-TWO }]
    panes.forEach(function (p, i) {
        var b = document.createElement('div'), name = p.dataset.tab || ('Part ' + (i + 1)), hw = /^homework/i.test(name);
        b.className = 't-tab'; b.setAttribute('role', 'tab'); b.tabIndex = 0;
        b.innerHTML = '<span class="tn"></span><span class="tr"></span><span class="tq"></span>';
        b.querySelector('.tn').textContent = hw ? (name.replace(/^homework\s*/i, '') || name) : name;
        b.dataset.i = i;
        var qs = [], seen = {};
        p.querySelectorAll('[name]').forEach(function (el) {
            var nm = el.name.replace(/\[\]$/, ''), m = nm.match(/^p\d+_q([\d_]+)$/);
            if (!m || seen[nm]) return;
            seen[nm] = true;
            m[1].split('_').forEach(function (n, k) { qs.push({ n: +n, name: nm, k: k }); });
        });
        qnav[i] = qs;
        if (qs.length) {
            var lo = qs[0].n, hi = qs[qs.length - 1].n;
            b.querySelector('.tr').textContent = lo === hi ? lo : lo + '–' + hi;
            qs.forEach(function (q) {
                var d = document.createElement('button');
                d.type = 'button'; d.className = 'qn'; d.textContent = q.n; d.title = 'Question ' + q.n;
                d.addEventListener('click', function (e) { e.stopPropagation(); goQuestion(i, q.name); });
                b.querySelector('.tq').appendChild(d);
            });
        }
        b.addEventListener('click', function () { show(i); });
        b.addEventListener('keydown', function (e) { if (e.target === b && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); show(i); } });
        (hw ? hwList : list).appendChild(b);
        if (hw) hwList.hidden = false;
    });
    function goQuestion(i, name) {
        if (current !== i) { show(i); if (current !== i) return; } // a locked exercise stays shut
        var el = panes[i].querySelector('[name="' + name + '"], [name="' + name + '[]"]');
        if (!el) return;
        var box = el.closest('.hd-zone, .lp, .sm-gap, .mc-q, .q-row, label') || el;
        box.scrollIntoView({ block: 'center', behavior: 'smooth' });
        var f = el.offsetParent ? el : box.querySelector('button, [tabindex]');
        if (f && f.focus) setTimeout(function () { f.focus({ preventScroll: true }); }, 250);
    }
    function answeredCount(pane, name) {
        var els = pane.querySelectorAll('[name="' + name + '"], [name="' + name + '[]"]'), c = 0;
        els.forEach(function (el) {
            if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) c++; }
            else if (String(el.value).trim()) c++;
        });
        return c;
    }
    function refreshQnav() {
        panes.forEach(function (p, i) {
            var btns = tabEl(i) ? tabEl(i).querySelectorAll('.qn') : [];
            (qnav[i] || []).forEach(function (q, k) {
                if (btns[k]) btns[k].classList.toggle('filled', answeredCount(p, q.name) > q.k);
            });
        });
    }
    window.tRefreshQnav = refreshQnav;
    ['input', 'change'].forEach(function (ev) { document.addEventListener(ev, function () { setTimeout(refreshQnav, 0); }, true); });
    setTimeout(refreshQnav, 50); // after answers saved in this browser are put back
    function show(i, force) {
        if (!force && gate && !gate(i)) return;
        current = i;
        panes.forEach(function (p, k) { p.classList.toggle('active', k === i); });
        bar.querySelectorAll('.t-tab').forEach(function (b) { var on = +b.dataset.i === i; b.classList.toggle('active', on); if (on) b.scrollIntoView({ block: 'nearest', inline: 'nearest' }); });
        bar.querySelector('.prev').disabled = i === 0;
        bar.querySelector('.next').disabled = i === panes.length - 1;
        if (window.tAfterShow) window.tAfterShow(i);
    }
    bar.querySelector('.prev').addEventListener('click', function () { if (current > 0) show(current - 1); });
    bar.querySelector('.next').addEventListener('click', function () { if (current < panes.length - 1) show(current + 1); });
    if (panes.length) show(0);

    /* ---------- Timer + finish ----------
       Panes with data-minutes each get their own countdown (it runs only while that exercise is open).
       When an exercise's time is up it locks and the next one opens; when all are done the test finishes.
       Otherwise one timer covers the whole test. Remaining time survives a page reload. */
    var timer = document.querySelector('.t-timer'), left = (+timer.dataset.minutes) * 60, tick, finished = false, submitting = null;
    var noTimer = @json((bool) $noTimer);
    var timed = !noTimer && panes.some(function (p) { return p.dataset.minutes; });
    var store = 'tl2:' + location.pathname + ':' + @json(auth()->id()) + @json(! empty($practice) ? ':practice' : '');
    // base = the minutes each clock was started with; when a teacher changes the minutes the difference is added
    var base = panes.map(function (p) { return p.dataset.minutes ? +p.dataset.minutes : null; }), allBase = +timer.dataset.minutes;
    var paneLeft = base.map(function (m) { return m === null ? null : m * 60; });
    try {
        var saved = JSON.parse(localStorage.getItem(store) || 'null');
        if (saved && saved.left && saved.left.length === paneLeft.length) {
            paneLeft = saved.left.map(function (v, i) { return v === null || base[i] === null ? paneLeft[i] : v + (base[i] - saved.base[i]) * 60; });
        }
        if (saved && typeof saved.allLeft === 'number') left = saved.allLeft + (allBase - saved.allBase) * 60;
    } catch (e) {}
    function save() { try { localStorage.setItem(store, JSON.stringify({ left: paneLeft, base: base, allLeft: left, allBase: allBase })); } catch (e) {} }

    /* Answers typed so far are kept in this browser, so a refresh does not lose them (cleared on finish). */
    var ansKey = store + ':ans';
    function saveAnswers() {
        var data = {};
        document.querySelectorAll('.t-tabs [name]').forEach(function (el) {
            if (el.type === 'radio' || el.type === 'checkbox') { if (el.checked) (data[el.name] = data[el.name] || []).push(el.value); }
            else if (String(el.value).trim()) data[el.name] = el.value;
        });
        try { localStorage.setItem(ansKey, JSON.stringify(data)); } catch (e) {}
    }
    var ansTimer = null;
    ['input', 'change'].forEach(function (ev) {
        document.querySelector('.t-tabs') && document.querySelector('.t-tabs').addEventListener(ev, function () {
            clearTimeout(ansTimer); ansTimer = setTimeout(saveAnswers, 200);
        });
    });
    setTimeout(function () { // after the dropdown / drag & drop pickers are built
        var data = null;
        try { data = JSON.parse(localStorage.getItem(ansKey) || 'null'); } catch (e) {}
        if (!data) return;
        Object.keys(data).forEach(function (name) {
            document.querySelectorAll('.t-tabs [name="' + name + '"]').forEach(function (el) {
                if (el.type === 'radio' || el.type === 'checkbox') el.checked = [].concat(data[name]).indexOf(el.value) > -1;
                else el.value = data[name];
                if (el.tagName === 'SELECT' || el.type === 'radio' || el.type === 'checkbox') el.dispatchEvent(new Event('change', { bubbles: true }));
            });
        });
    }, 0);
    function fmt(sec) { sec = Math.max(0, sec); var m = Math.floor(sec / 60), s = sec % 60; return m + ':' + (s < 10 ? '0' : '') + s; }
    function unlockPane(i) {
        var p = panes[i];
        if (!p.classList.contains('timeup')) return;
        p.classList.remove('timeup');
        p.querySelectorAll('input, select, textarea').forEach(function (el) { el.readOnly = false; el.removeAttribute('tabindex'); });
        var tab = tabEl(i);
        if (tab) tab.classList.remove('done');
        var note = p.querySelector('.timeup-note'); if (note) note.remove();
    }
    function toast(text) {
        var t = document.createElement('div');
        t.className = 'time-toast'; t.innerHTML = '<span class="material-icons-outlined">more_time</span>' + text;
        document.body.appendChild(t);
        setTimeout(function () { t.classList.add('out'); setTimeout(function () { t.remove(); }, 400); }, 5000);
    }
    /* Teacher changed the minutes for this batch: move every clock by the difference. */
    function applyTimes(times) {
        if (finished || !times || noTimer) return;
        var msgs = [];
        if (timed) {
            panes.forEach(function (p, i) {
                var m = times[String(p.dataset.part || i + 1)];
                if (m === undefined || base[i] === null || +m === base[i]) return;
                var diff = +m - base[i];
                paneLeft[i] += diff * 60; base[i] = +m;
                if (paneLeft[i] > 0) unlockPane(i);
                msgs.push((p.dataset.tab || 'Part ' + (i + 1)) + ' ' + (diff > 0 ? '+' : '') + diff + ' min');
            });
            if (msgs.length && (paneLeft[current] === null || paneLeft[current] <= 0)) { var n = nextOpen(); if (n > -1) show(n); }
            timer.querySelector('.v').textContent = paneLeft[current] === null ? '—' : fmt(paneLeft[current]);
        } else if (times.all !== undefined && +times.all !== allBase) {
            var d = +times.all - allBase;
            left += d * 60; allBase = +times.all;
            msgs.push((d > 0 ? '+' : '') + d + ' min');
            timer.querySelector('.v').textContent = fmt(left);
        }
        if (msgs.length) { save(); toast('Your teacher changed the time: ' + msgs.join(', ')); }
    }
    function lockPane(i) {
        var p = panes[i];
        if (p.classList.contains('timeup')) return;
        p.classList.add('timeup');
        p.querySelectorAll('input, select, textarea').forEach(function (el) { el.readOnly = true; el.tabIndex = -1; });
        var tab = tabEl(i);
        if (tab) tab.classList.add('done');
        var note = document.createElement('div');
        note.className = 'timeup-note';
        note.innerHTML = '<span class="material-icons-outlined">lock_clock</span><span class="nt">' + (paneLeft[i] > 0 ? 'You finished ' : 'Time is up for ') + (p.dataset.tab || 'this part') + '. Your answers here are saved.</span>';
        p.insertBefore(note, p.firstChild);
        if (exMode) addPracticeBtn(note, i);
    }
    paneLeft.forEach(function (v, i) { if (v !== null && v <= 0) lockPane(i); });
    function nextOpen() { for (var k = 0; k < panes.length; k++) if (paneLeft[k] !== null && paneLeft[k] > 0) return k; return -1; }
    /* One exercise at a time: the next exercise opens only after the one before it is finished
       (its time ran out, or the student pressed "Finish exercise"). Finished ones can be looked at, not changed. */
    var doneSet = [];
    try { doneSet = JSON.parse(localStorage.getItem(store + ':done') || '[]'); } catch (e) {}
    // homework exercises the server already has (handed in here or on another device) stay finished
    var serverDone = @json(array_map('intval', $doneParts ?? []));
    panes.forEach(function (p, k) { if (serverDone.indexOf(+p.dataset.part) > -1 && doneSet.indexOf(k) === -1) doneSet.push(k); });
    function isDone(k) { return paneLeft[k] === null ? doneSet.indexOf(k) > -1 : paneLeft[k] <= 0 || doneSet.indexOf(k) > -1; }
    function markDone(k) {
        if (doneSet.indexOf(k) === -1) doneSet.push(k);
        try { localStorage.setItem(store + ':done', JSON.stringify(doneSet)); } catch (e) {}
        lockPane(k);
        refreshTabs();
    }
    function reachable(i) { for (var k = 0; k < i; k++) if (!isDone(k)) return false; return true; }
    function refreshTabs() {
        panes.forEach(function (p, k) {
            var t = tabEl(k); if (!t) return;
            var ok = reachable(k);
            t.classList.toggle('locked', !ok);
            t.title = ok ? '' : 'Finish the exercise before this one first';
        });
    }
    // Exercise classes (tabs "Exercise 1", "Warm-up" …) and homework (tabs "Homework Ex 1" …): every exercise is
    // finished and marked on its own, and a student opens the next one only after finishing the one before.
    // Full tests ("Passage" tabs) stay free and are handed in as a whole with "Finish test".
    var exMode = panes.length > 1 && panes.every(function (p) { return !/^passage/i.test(p.dataset.tab || ''); });
    var sequential = exMode && @json((bool) auth()->user()?->isStudent() && empty($practice)); // practice: every exercise open
    if (sequential) {
        gate = function (i) {
            if (reachable(i)) return true;
            toast('Finish ' + (panes[current].dataset.tab || 'this exercise') + ' first — the next exercise opens after it.');
            return false;
        };
        doneSet.forEach(function (k) { if (panes[k]) lockPane(k); });
        var fin = document.createElement('button');
        fin.type = 'button'; fin.className = 't-next-ex';
        bar.insertBefore(fin, bar.querySelector('.t-arrow.prev'));
        window.tAfterShow = function (i) {
            var last = i === panes.length - 1;
            fin.hidden = true; // the top "Finish …" button hands in the open exercise
            fin.innerHTML = 'Finish ' + (panes[i].dataset.tab || 'exercise') + ' <span class="material-icons-outlined">arrow_forward</span>';
            bar.querySelector('.next').disabled = last || !isDone(i);
        };
        fin.addEventListener('click', function () {
            var i = current;
            if (!confirm('Finish ' + (panes[i].dataset.tab || 'this exercise') + ' and go to the next one?\nYou will not be able to change these answers.')) return;
            markDone(i);
            show(i + 1, true);
        });
        refreshTabs();
        window.tAfterShow(current);
    }
    if (timed) {
        var first = nextOpen();
        for (var k = 0; k < panes.length; k++) { if (!isDone(k)) { first = k; break; } }
        if (first > 0) show(first, true);
        timer.querySelector('.v').textContent = fmt(paneLeft[current] !== null ? paneLeft[current] : 0);
    } else {
        timer.querySelector('.v').textContent = fmt(left);
        if (sequential) { for (var k3 = 0; k3 < panes.length; k3++) { if (!isDone(k3)) { if (k3 > 0) show(k3, true); break; } } }
    }
    var backUrl = @json($backUrl ?? route(strtolower($module)));
    function countAnswered() {
        var fields = {};
        document.querySelectorAll('.t-tabs [name]').forEach(function (el) {
            if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) { fields[el.name] = fields[el.name] || false; return; }
            fields[el.name] = fields[el.name] || !!String(el.value).trim();
        });
        var n = 0; Object.keys(fields).forEach(function (k) { if (fields[k]) n++; });
        return { answered: n, total: Object.keys(fields).length };
    }
    // when this attempt started (kept across reloads) – for the time shown on the result
    var startedAt = Date.now();
    try { startedAt = +localStorage.getItem(store + ':start') || startedAt; localStorage.setItem(store + ':start', startedAt); } catch (e) {}
    var tookMs = 0;
    var onResClose = null;
    function closeResult() {
        document.querySelector('.t-res').classList.remove('open');
        var f = onResClose; onResClose = null; if (f) f();
    }
    document.querySelector('.t-res .res-x').addEventListener('click', closeResult);
    document.querySelector('.t-res .res-done').addEventListener('click', closeResult);
    /* After finishing: every answer box gets its number (green / red), a ✓ / ✗ and the correct answer. */
    var ansMatch = (function () {
        function clean(v) { return String(v || '').replace(/[‘’]/g, "'").replace(/[–—]/g, '-').replace(/\s+/g, ' ').trim().toLowerCase(); }
        function esc(v) { return v.replace(/[.*+?^${}()|[\]\\\/]/g, '\\$&'); }
        function words(v) {
            return v.split(/(\s+)/).map(function (t) {
                if (t.trim() === '') return t === '' ? '' : '\\s*';
                var o = t.split('/').map(esc);
                return o.length > 1 ? '(?:' + o.join('|') + ')' : o[0];
            }).join('');
        }
        function pattern(alt) {
            return alt.split(/(\s*\([^)]*\))/).filter(function (x) { return x !== ''; }).map(function (part) {
                var m = part.match(/^(\s*)\(([^)]*)\)$/);
                return m ? '(?:' + (m[1] ? '\\s*' : '') + words(m[2].trim()) + ')?' : words(part);
            }).join('');
        }
        function set(v) { var a = clean(v).split(/[,\s]+/).filter(Boolean).sort(); return a.filter(function (x, k) { return a.indexOf(x) === k; }); }
        return function (key, value, multi) {
            if (multi) { var w = set(key), g = set(value); return g.length === w.length && w.every(function (x) { return g.indexOf(x) > -1; }); }
            value = clean(value);
            if (!value) return false;
            return String(key).trim().split(/\s+\/\s+/).some(function (alt) {
                try { return new RegExp('^' + pattern(clean(alt)) + '$', 'i').test(value); } catch (e) { return clean(alt) === value; }
            });
        };
    })();
    function setMark(mk, ok) {
        if (!mk) return;
        mk.className = 'ans-mark ' + (ok ? 'ok' : 'bad');
        mk.querySelector('.ans-tick').textContent = ok ? '✓' : '✗';
    }
    function markInline(rows, root, practice) {
        rows.forEach(function (x) {
            var els = root.querySelectorAll('[name="' + x.name + '"], [name="' + x.name + '[]"]');
            if (!els.length) return;
            var first = els[0], mark = function () {
                var m = document.createElement('span');
                m.className = 'ans-mark ' + (x.ok ? 'ok' : 'bad');
                m.innerHTML = '<span class="ans-num"></span><span class="ans-tick">' + (x.ok ? '✓' : '✗') + '</span>';
                m.firstChild.textContent = x.q;
                return m;
            }, txt = function (v) {
                var t = document.createElement('span'); t.className = 'ans-txt';
                t.innerHTML = 'Answer: <b></b>'; t.querySelector('b').textContent = v;
                return t;
            };
            if (first.type === 'radio' || first.type === 'checkbox') {
                var full = String(x.correct).toUpperCase().trim(), want = full.split(/[,\s]+/);
                els.forEach(function (el) {
                    if (!practice) el.disabled = true;
                    var lab = el.closest('label'), v = el.value.toUpperCase();
                    var right = v === full || want.indexOf(v) > -1;
                    if (lab && right) lab.classList.add('is-answer');
                    var picked = String(x.given || '').toUpperCase().split(/\s*,\s*/).indexOf(v) > -1;
                    if (lab && picked && !right) {
                        lab.classList.add('is-wrong');
                        var tag = document.createElement('span');
                        tag.className = 'your-tag';
                        tag.innerHTML = 'Your answer <b>✗</b>';
                        lab.appendChild(tag);
                    }
                });
                var q = first.closest('.mc-q');
                if (q && practice) els.forEach(function (el) {
                    el.addEventListener('change', function () {
                        var got = [].filter.call(els, function (e) { return e.checked; }).map(function (e) { return e.value; }).join(',');
                        var ok = ansMatch(x.correct, got, true);
                        q.classList.toggle('ans-ok', ok); q.classList.toggle('ans-bad', !ok);
                    });
                });
                if (q) {
                    q.classList.add(x.ok ? 'ans-ok' : 'ans-bad');
                    var stem = q.querySelector('.mc-stem') || q;
                    if (!stem.querySelector('.ans-txt')) stem.appendChild(txt(x.correct));
                }
                return;
            }
            els.forEach(function (el) {
                if (!practice) { el.disabled = true; el.classList.add(x.ok ? 'ans-ok' : 'ans-bad'); }
                var box = el.closest('.lp, .sm-gap, .hd-zone') || el, val = x.correct;
                if (box._sm && box._sm.opts[x.correct] !== undefined) val = box._sm.opts[x.correct];
                else if (box._hd && box._hd.opts[x.correct]) val = x.correct + ' ' + box._hd.opts[x.correct].text;
                // number + ✓/✗, the answer box and the correct answer stay together as one tidy group
                var grp = document.createElement('span');
                grp.className = 'ans-group';
                box.before(grp);
                grp.appendChild(mark()); grp.appendChild(box);
                if (practice && !x.ok) {
                    var yv = String(x.given || '').trim(), yours = document.createElement('span');
                    if (yv && box._sm && box._sm.opts[yv] !== undefined) yv = box._sm.opts[yv];
                    else if (yv && box._hd && box._hd.opts[yv]) yv = yv + ' ' + box._hd.opts[yv].text;
                    yours.className = 'ans-yours';
                    yours.innerHTML = 'Your answer: <b></b>';
                    yours.querySelector('b').textContent = yv || 'no answer';
                    grp.appendChild(yours);
                }
                if (practice || !x.ok) grp.appendChild(txt(val));
                if (practice) {
                    var mk = grp.firstChild, multi = x.name.split('_').length > 2;
                    var upd = function () { if (mk.isConnected) setMark(mk, ansMatch(x.correct, el.value, multi)); };
                    el.addEventListener('input', upd); el.addEventListener('change', upd);
                }
            });
        });
    }
    function showResult(j, title) {
        var r = document.querySelector('.t-res'), esc = function (v) { var x = document.createElement('div'); x.textContent = v; return x.innerHTML; };
        var ok = j.rows.filter(function (x) { return x.ok; }).length, bad = j.rows.length - ok;
        var s = Math.round(tookMs / 1000), p = function (n) { return (n < 10 ? '0' : '') + n; };
        r.querySelector('.res-title').textContent = title || 'Test result';
        r.querySelector('.res-sub').textContent = @json(($class['title'] ?? null) ?: ($title ?? ''));
        r.querySelector('.v-band').textContent = j.band !== null && j.band !== undefined ? j.band : '—';
        r.querySelector('.v-score').textContent = j.score + '/' + j.total;
        r.querySelector('.v-ok').textContent = ok;
        r.querySelector('.v-bad').textContent = bad;
        r.querySelector('.v-time').textContent = (s >= 3600 ? Math.floor(s / 3600) + ':' + p(Math.floor(s / 60) % 60) : Math.floor(s / 60)) + ':' + p(s % 60);
        var bar = r.querySelector('.res-bar i'); bar.style.width = '0';
        setTimeout(function () { bar.style.width = (j.total ? Math.round(j.score / j.total * 100) : 0) + '%'; }, 60);
        r.querySelector('tbody').innerHTML = j.rows.map(function (x) {
            return '<tr class="' + (x.ok ? 'ok' : 'bad') + '"><td><span class="qno">' + esc(x.q) + '</span></td>' +
                '<td class="given">' + (String(x.given).trim() ? esc(x.given) : '<span class="empty">No answer</span>') + '</td>' +
                '<td class="correct">' + esc(x.correct) + '</td>' +
                '<td><span class="st material-icons-outlined">' + (x.ok ? 'check' : 'close') + '</span></td></tr>';
        }).join('');
        document.querySelector('.t-done').classList.remove('open');
        r.classList.add('open');
        r.querySelector('.res-done').focus();
    }
    /* Homework handed in: a short message from Hexa's Zindabazar. Each student gets their own one – the pick
       depends on who they are and which homework / exercise it is – so friends do not all see the same words. */
    var moti = document.querySelector('.t-moti');
    var motiMsgs = [
        ['🌟', 'Well done, {name}!', 'Every exercise you finish is a step closer to your target band. Keep this energy going!'],
        ['🚀', 'You are on your way, {name}!', 'Consistency beats talent. Showing up for your homework like this is exactly how high scorers are made.'],
        ['💪', 'Great effort, {name}!', 'Mistakes today are lessons for tomorrow. Check your answers, learn from them, and you will only get stronger.'],
        ['🎯', 'Target locked, {name}!', 'Your hard work is building real reading skills. Stay focused — your dream score is closer than you think.'],
        ['📚', 'Proud of you, {name}!', 'Reading a little every day trains your mind to read faster and smarter. You are doing it right!'],
        ['🏆', 'Champion move, {name}!', 'Finishing homework on time shows real discipline. That discipline will carry you through the IELTS exam.'],
        ['✨', 'Keep shining, {name}!', 'Every passage you read adds new words and new confidence. Small steps, big results.'],
        ['🔥', 'You are on fire, {name}!', 'Practice is the bridge between where you are and where you want to be. You just crossed another plank.'],
        ['🌱', 'Growing every day, {name}!', 'Progress is not always loud, but it is always happening when you keep practising like this.'],
        ['🙌', 'Fantastic work, {name}!', 'Believe in yourself. The effort you put in today is the band score you will see tomorrow.'],
        ['⭐', 'Another one done, {name}!', 'Your teachers at Hexa\'s are cheering for you. Keep learning, keep improving, keep going!'],
        ['🧠', 'Sharper than yesterday, {name}!', 'Each question you answer trains your brain to find information faster. That is the secret of IELTS Reading.'],
        ['🎉', 'Bravo, {name}!', 'Hard work + regular practice = success. You have both. Now keep the streak alive!'],
        ['🌈', 'Nice job, {name}!', 'Do not worry about a few wrong answers — that is how learning works. What matters is that you never stop.'],
        ['💡', 'Smart work, {name}!', 'Look back at the questions you missed and find the keywords in the passage. That habit will boost your score.'],
        ['🥇', 'Way to go, {name}!', 'Great students are made one homework at a time. Today you added one more to your success story.']
    ];
    var motiName = @json(trim(explode(' ', (string) (auth()->user()?->name ?? ''))[0] ?? '') ?: 'Champion');
    function motivate(part, then) {
        if (!moti) { if (then) then(); return; }
        var seed = @json((string) auth()->id()) + ':' + location.pathname + ':' + part, h = 0;
        for (var k = 0; k < seed.length; k++) h = (h * 31 + seed.charCodeAt(k)) >>> 0;
        h = (h ^ (h >>> 16)) >>> 0; h = Math.imul(h, 0x45d9f3b) >>> 0; h = (h ^ (h >>> 16)) >>> 0; // scatter, so neighbours get different words (kept unsigned)
        var m = motiMsgs[h % motiMsgs.length] || motiMsgs[0];
        moti.querySelector('.mo-emoji').textContent = m[0];
        moti.querySelector('.mo-title').textContent = m[1].replace('{name}', motiName);
        moti.querySelector('.mo-text').textContent = m[2].replace('{name}', motiName);
        moti.classList.add('open');
        var ok = moti.querySelector('.mo-ok'), closed = false;
        ok.focus();
        var close = function () {
            if (closed) return; closed = true; clearTimeout(auto);
            moti.classList.remove('open'); if (then) then();
        };
        ok.onclick = close;
        var auto = setTimeout(close, 5000); // closes by itself after 5 s; "Thank you!" closes it sooner
    }
    window.tMotivate = motivate;

    /* Exercise classes: hand in one exercise (tab) only, show its result, then open the next exercise. */
    var exSent = {};
    function submitExercise(i, auto) {
        if (exSent[i]) return;
        exSent[i] = true;
        var pane = panes[i], fd = new FormData(), m = (pane.querySelector('[name^="p"]') || {}).name;
        m = m && m.match(/^p(\d+)_/);
        pane.querySelectorAll('[name]').forEach(function (el) {
            if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;
            fd.append(el.name, el.value);
        });
        if (m) fd.append('_part', m[1]);
        tookMs = Date.now() - startedAt;
        startedAt = Date.now();
        try { localStorage.setItem(store + ':start', startedAt); } catch (e) {}
        var wasDone = doneSet.indexOf(i) > -1;
        var next = -1;
        for (var k = i + 1; k < panes.length; k++) if (!exSent[k] && doneSet.indexOf(k) === -1 && !(paneLeft[k] !== null && paneLeft[k] <= 0)) { next = k; break; }
        if (wasDone) next = -1; // finishing an exercise again: stay on it
        markDone(i);
        if (next === -1) { // every exercise handed in: the class is over
            finished = true; clearInterval(tick);
            try { localStorage.removeItem(store); localStorage.removeItem(store + ':done'); localStorage.removeItem(store + ':start'); localStorage.removeItem(ansKey); } catch (e) {}
        }
        document.querySelector('.t-finish').disabled = true;
        @if (! empty($submit))
        fetch(@json($submit), { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw r; return r.json(); })
            .then(function (j) {
                var after = function () {
                    if (j.rows) {
                        marks[i] = { rows: j.rows, score: j.score, total: j.total };
                        saveMarks();
                        practicePane(i, j.rows);
                    }
                    if (next > -1) show(next, true);
                };
                var reveal = function () {
                    if (j.rows && j.rows.length) { onResClose = after; showResult(j, (pane.dataset.tab || 'Exercise') + ' result'); }
                    else { toast((pane.dataset.tab || 'Exercise') + ' submitted.'); after(); }
                };
                // homework: the message from Hexa's comes first, right after handing in; "Thank you!" opens the result
                if (moti) motivate(m ? m[1] : i, reveal); else reveal();
            })
            .catch(function () { toast('Could not save ' + (pane.dataset.tab || 'this exercise') + ' — please tell your teacher.'); });
        @else
        if (moti) motivate(m ? m[1] : i, function () { if (next > -1) show(next, true); });
        else if (next > -1) show(next, true);
        @endif
    }
    /* Marked exercises are kept per student + page, so the answers stay beside the boxes after a reload. */
    var marksKey = store + ':marks', marks = {};
    try { marks = JSON.parse(localStorage.getItem(marksKey) || '{}') || {}; } catch (e) { marks = {}; }
    function saveMarks() { try { localStorage.setItem(marksKey, JSON.stringify(marks)); } catch (e) {} }
    function restoreMarks() {
        Object.keys(marks).forEach(function (k) {
            var i = +k, p = panes[i], m = marks[k];
            if (!p || !m || !m.rows) return;
            if (doneSet.indexOf(i) === -1) doneSet.push(i);
            practicePane(i, m.rows);
        });
        if (sequential) refreshTabs();
    }

    function clearMarks(p) {
        p.querySelectorAll('.ans-group').forEach(function (g) { var box = g.children[1]; if (box) g.before(box); g.remove(); });
        p.querySelectorAll('.ans-txt, .ans-mark, .ans-yours, .your-tag, .retest-bar').forEach(function (el) { el.remove(); });
        p.querySelectorAll('.mc-q').forEach(function (q) { q.classList.remove('ans-ok', 'ans-bad'); });
        p.querySelectorAll('label.is-answer, label.is-wrong').forEach(function (l) { l.classList.remove('is-answer', 'is-wrong'); });
    }
    /* A marked exercise: every box shows its number, ✓ / ✗ and the correct answer beside it, and the boxes are
       empty and open again so the student can practise; "Finish" marks the new try. */
    function practicePane(i, rows) {
        var p = panes[i];
        clearMarks(p);
        p.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name) return;
            el.disabled = false; el.readOnly = false;
            el.classList.remove('ans-ok', 'ans-bad');
            if (el.type === 'radio' || el.type === 'checkbox') el.checked = false;
            else el.value = '';
            el.dispatchEvent(new Event(el.tagName === 'SELECT' ? 'change' : 'input', { bubbles: true }));
        });
        unlockPane(i);
        markInline(rows, p, true);
        p.classList.add('marked');
        var ok = rows.filter(function (x) { return x.ok; }).length, qs = p.querySelector('.t-questions') || p;
        var bar = document.createElement('div');
        bar.className = 'retest-bar';
        bar.innerHTML = '<span class="rt-score"><span class="material-icons-outlined">fact_check</span>Last try: <b>' + ok + ' / ' + rows.length + '</b></span>' +
            '<button type="button" class="retest-btn" title="Clear everything and take this exercise again"><span class="material-icons-outlined">restart_alt</span>Re-test</button>';
        bar.querySelector('.retest-btn').addEventListener('click', function () { resetPane(i); });
        qs.insertBefore(bar, qs.firstChild);
        exSent[i] = false;
        if (sequential) refreshTabs();
        if (window.tAfterShow) window.tAfterShow(current);
    }

    /* "Practice again": empty a finished exercise and do it once more (the new try is marked and saved again). */
    function addPracticeBtn(note, i) {
        var b = document.createElement('button');
        b.type = 'button'; b.className = 'practice-btn';
        b.innerHTML = '<span class="material-icons-outlined">replay</span>Practice again';
        b.addEventListener('click', function () { resetPane(i); });
        note.appendChild(b);
    }
    function resetPane(i) {
        var p = panes[i];
        p.querySelectorAll('.ans-group').forEach(function (g) { var box = g.children[1]; if (box) g.before(box); g.remove(); });
        p.querySelectorAll('.ans-txt, .ans-mark, .ans-yours, .your-tag, .retest-bar').forEach(function (el) { el.remove(); });
        p.querySelectorAll('.mc-q').forEach(function (q) { q.classList.remove('ans-ok', 'ans-bad'); });
        p.querySelectorAll('label.is-answer, label.is-wrong').forEach(function (l) { l.classList.remove('is-answer', 'is-wrong'); });
        p.querySelectorAll('input, select, textarea').forEach(function (el) {
            if (!el.name) return;
            el.disabled = false;
            el.classList.remove('ans-ok', 'ans-bad');
            if (el.type === 'radio' || el.type === 'checkbox') el.checked = false;
            else el.value = '';
            el.dispatchEvent(new Event(el.tagName === 'SELECT' ? 'change' : 'input', { bubbles: true }));
        });
        p.classList.remove('marked');
        delete marks[i]; saveMarks();
        exSent[i] = false;
        doneSet = doneSet.filter(function (k) { return k !== i; });
        try { localStorage.setItem(store + ':done', JSON.stringify(doneSet)); } catch (e) {}
        if (paneLeft[i] !== null && base[i] !== null) paneLeft[i] = base[i] * 60;
        unlockPane(i);
        if (finished) { finished = false; clearInterval(tick); tick = setInterval(tickFn, 1000); }
        startedAt = Date.now();
        try { localStorage.setItem(store + ':start', startedAt); } catch (e) {}
        if (sequential) refreshTabs();
        show(i, true);
        if (timed) timer.querySelector('.v').textContent = fmt(paneLeft[i] !== null ? paneLeft[i] : 0);
        toast((p.dataset.tab || 'Exercise') + ': re-test started — answer it again from the beginning.');
    }
    if (exMode) {
        var prevAfter = window.tAfterShow, fb = document.querySelector('.t-finish');
        window.tAfterShow = function (i) {
            if (prevAfter) prevAfter(i);
            fb.textContent = 'Finish ' + (panes[i].dataset.tab || 'exercise');
            fb.disabled = !!exSent[i];
        };
        // after the drag & drop / dropdown pickers are built, so restored answers show in them too
        setTimeout(function () { restoreMarks(); window.tAfterShow(current); }, 0);
        window.tAfterShow(current);
    }
    function finish(auto, stay) {
        if (finished) return submitting || Promise.resolve();
        finished = true;
        clearInterval(tick);
        tookMs = Date.now() - startedAt;
        try { localStorage.removeItem(store); localStorage.removeItem(store + ':done'); localStorage.removeItem(store + ':start'); localStorage.removeItem(ansKey); } catch (e) {}
        var fields = {}, answered = 0;
        document.querySelectorAll('.t-tabs [name]').forEach(function (el) {
            if ((el.type === 'radio' && !el.checked)) { fields[el.name] = fields[el.name] || false; return; }
            fields[el.name] = !!String(el.value).trim();
        });
        Object.keys(fields).forEach(function (k) { if (fields[k]) answered++; });
        var d = document.querySelector('.t-done'), msg = d.querySelector('.msg');
        msg.textContent = (auto ? "Time's up. " : '') + 'You answered ' + answered + ' of ' + Object.keys(fields).length + ' questions.';
        d.classList.add('open');
        d.querySelector('h3').textContent = auto ? "Time's up" : 'Test finished';
        document.querySelector('.t-finish').disabled = true;
        @if (! empty($submit))
        var fd = new FormData();
        document.querySelectorAll('.t-tabs [name]').forEach(function (el) {
            if ((el.type === 'radio' || el.type === 'checkbox') && !el.checked) return;
            fd.append(el.name, el.value);
        });
        msg.textContent += ' Saving…';
        if (!stay) { d.classList.add('busy'); d.querySelector('h3').textContent = 'Submitting your test…'; d.querySelector('.t-back').hidden = true; }
        return submitting = fetch(@json($submit), { method: 'POST', body: fd, headers: { 'X-CSRF-TOKEN': @json(csrf_token()), 'Accept': 'application/json' } })
            .then(function (r) { if (!r.ok) throw r; return r.json(); })
            .then(function (j) {
                msg.textContent = msg.textContent.replace(' Saving…', ' Your answers have been submitted.');
                if (j.total) msg.textContent += ' Score: ' + j.score + ' / ' + j.total + (j.band !== null ? ' · Band ' + j.band : '');
                if (!stay && j.rows && j.rows.length) { // reading: result popup right away, then the answers on the page
                    onResClose = function () {
                        markInline(j.rows, document);
                        var banner = document.createElement('div');
                        banner.className = 'ans-banner';
                        banner.innerHTML = '<span class="material-icons-outlined">fact_check</span><span>Your score: <b>' + j.score + ' / ' + j.total + '</b>' + (j.band !== null ? ' · Band <b>' + j.band + '</b>' : '') + '</span><button type="button" class="t-btn practice-btn" style="margin-left:auto"><span class="material-icons-outlined">replay</span>Practice again</button><a class="t-btn tone" style="text-decoration:none">Back</a>';
                        banner.querySelector('a').href = backUrl;
                        banner.querySelector('.practice-btn').addEventListener('click', function () { location.reload(); });
                        document.querySelector('.t-head').after(banner);
                    };
                    if (moti) motivate('all', function () { showResult(j); }); else showResult(j);
                    return;
                }
                if (!stay && moti) { motivate('all', function () { location.href = j.result_url || backUrl; }); return; }
                if (!stay) location.href = j.result_url || backUrl;  // like CD IELTS: back to the index, which shows "submitted successfully"
            })
            .catch(function () {
                d.classList.remove('busy'); d.querySelector('h3').textContent = 'Not submitted'; d.querySelector('.t-back').hidden = false;
                msg.textContent = msg.textContent.replace(' Saving…', ' Could not save your answers — please tell your teacher.');
            });
        @else
        if (!stay && moti) motivate('all'); // nothing is sent from this page, but finishing still gets the message
        return Promise.resolve();
        @endif
    }
    function tickFn() {
        if (noTimer) return;
        if (!timed) {
            left--;
            if (left % 5 === 0) save();
            timer.querySelector('.v').textContent = fmt(left);
            timer.classList.toggle('low', left <= 300);
            if (left <= 0) finish(true);
            return;
        }
        // per-exercise mode: only the open exercise's clock runs
        var i = current;
        if (paneLeft[i] === null || paneLeft[i] <= 0) {
            timer.querySelector('.v').textContent = paneLeft[i] === null ? '—' : '0:00';
            timer.classList.remove('low');
            return;
        }
        paneLeft[i]--;
        save();
        timer.querySelector('.v').textContent = fmt(paneLeft[i]);
        timer.classList.toggle('low', paneLeft[i] <= 60);
        if (paneLeft[i] <= 0) {
            if (exMode) { submitExercise(i, true); return; }
            lockPane(i);
            var n = -1;
            for (var k2 = 0; k2 < panes.length; k2++) if (!isDone(k2)) { n = k2; break; }
            if (n === -1) finish(true); else show(n, true);
        }
    }
    tick = setInterval(tickFn, 1000);
    // switching tab updates the clock right away
    list.addEventListener('click', function () {
        if (timed) timer.querySelector('.v').textContent = paneLeft[current] === null ? '—' : fmt(paneLeft[current]);
    });

    /* ---------- Live updates from the teacher: time changes and shown answers (polled every 10 s) ---------- */
    @if (! empty($liveUrl))
    var shownDone = false;
    function renderAnswers(j) {
        shownDone = true;
        document.querySelector('.t-done').classList.remove('open');
        document.body.classList.add('answers-shown');
        var banner = document.createElement('div');
        banner.className = 'ans-banner';
        banner.innerHTML = '<span class="material-icons-outlined">fact_check</span><span>Your teacher is showing the answers.' +
            (j.total ? ' Your score: <b>' + j.score + ' / ' + j.total + '</b>' : '') + '</span>';
        document.querySelector('.t-head').after(banner);
        Object.keys(j.answers).forEach(function (name) {
            var els = document.querySelectorAll('.t-tabs [name="' + name + '"], .t-tabs [name="' + name + '[]"]');
            if (!els.length) return;
            var ok = j.detail ? j.detail[name] : undefined;
            var first = els[0];
            if (first.type === 'radio' || first.type === 'checkbox') {
                var full = String(j.answers[name]).toUpperCase().trim(), want = full.split(/[,\s]+/);
                els.forEach(function (el) {
                    el.disabled = true;
                    var lab = el.closest('label'), v = el.value.toUpperCase();
                    if (lab && (v === full || want.indexOf(v) > -1)) lab.classList.add('is-answer');
                });
                var q = first.closest('.mc-q');
                if (q && ok !== undefined) q.classList.add(ok ? 'ans-ok' : 'ans-bad');
                return;
            }
            els.forEach(function (el) {
                el.disabled = true;
                if (ok !== undefined) el.classList.add(ok ? 'ans-ok' : 'ans-bad');
                var tag = document.createElement('span');
                tag.className = 'ans-key';
                tag.textContent = j.answers[name];
                el.after(tag);
            });
        });
    }
    function checkLive() {
        if (shownDone) return;
        fetch(@json($liveUrl), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (j) {
                if (!j || shownDone) return;
                applyTimes(j.times);
                if (!j.shown) return;
                // hand in whatever the student has written, then show the answers with their marking
                (finished ? (submitting || Promise.resolve()) : finish(false, true)).then(function () {
                    return fetch(@json($liveUrl), { headers: { 'Accept': 'application/json' } }).then(function (r) { return r.json(); });
                }).then(renderAnswers, function () { renderAnswers(j); });
            }).catch(function () {});
    }
    checkLive();
    setInterval(checkLive, 10000);
    @endif
    var cfm = document.querySelector('.t-confirm');
    function closeConfirm() { cfm.classList.remove('open'); document.querySelector('.t-finish').focus(); }
    document.querySelector('.t-finish').addEventListener('click', function () {
        if (finished && !exMode) return;
        if (exMode) {
            if (!exSent[current]) submitExercise(current, false); // no "are you sure?" box: finish right away
            return;
        }
        var c = countAnswered(), left2 = c.total - c.answered, cnt = cfm.querySelector('.cnt');
        cnt.innerHTML = 'You have answered <b>' + c.answered + ' of ' + c.total + '</b> questions.' + (left2 > 0 ? ' <b>' + left2 + '</b> still empty.' : '');
        cnt.classList.toggle('warn', left2 > 0);
        cfm.classList.add('open');
        cfm.querySelector('.go').focus();
    });
    cfm.querySelector('.back').addEventListener('click', closeConfirm);
    cfm.addEventListener('click', function (e) { if (e.target === cfm) closeConfirm(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && cfm.classList.contains('open')) closeConfirm(); });
    cfm.querySelector('.go').addEventListener('click', function () { cfm.classList.remove('open'); finish(false); });

    /* ---------- Highlight & notes (like CD IELTS) ----------
       Select text (or right-click it) in the passage or questions -> menu: Highlight / Notes / Clear / Clear all.
       A note is a highlight with text attached; the Notes button lists them. */
    var popup = document.querySelector('.note-popup'), activeMark = null;
    var notesWrap = document.querySelector('.t-notes');
    var ctx = document.querySelector('.t-ctx'), ctxRange = null, gid = 0;
    var tabsRoot = document.querySelector('.t-tabs') || document.body;
    notesWrap.querySelector('button').addEventListener('click', function () { notesWrap.classList.toggle('open'); });
    document.addEventListener('click', function (e) { if (!notesWrap.contains(e.target)) notesWrap.classList.remove('open'); });

    function group(mk) { return mk.dataset.g ? [].slice.call(document.querySelectorAll('mark.hl[data-g="' + mk.dataset.g + '"]')) : [mk]; }
    function head(mk) { return group(mk)[0]; }
    function groupText(mk) { return group(mk).map(function (m) { return m.textContent; }).join(' ').replace(/\s+/g, ' ').trim(); }
    function unwrap(mk) { var p = mk.parentNode; if (!p) return; while (mk.firstChild) p.insertBefore(mk.firstChild, mk); p.removeChild(mk); p.normalize(); }
    function skip(node) { var el = node.parentNode; return !el || !el.closest || el.closest('input, textarea, select, button, .t-tablist, .t-ctx, .note-popup, .dnd-chip, .ans-key, .hd-opt, .hd-zone, .hd-hint'); }

    // wrap every text piece inside the range (works across paragraphs and list items)
    function wrapRange(range) {
        var root = range.commonAncestorContainer;
        if (root.nodeType === 3) root = root.parentNode;
        var nodes = [], w = document.createTreeWalker(root, NodeFilter.SHOW_TEXT, null);
        while (w.nextNode()) if (range.intersectsNode(w.currentNode) && w.currentNode.nodeValue.trim() && !skip(w.currentNode)) nodes.push(w.currentNode);
        var g = 'g' + Date.now() + (gid++), out = [];
        nodes.forEach(function (n) {
            var a = n === range.startContainer ? range.startOffset : 0;
            var b = n === range.endContainer ? range.endOffset : n.nodeValue.length;
            if (b <= a || !n.nodeValue.slice(a, b).trim()) return;
            if (n.parentNode.closest('mark.hl')) return;   // already highlighted
            if (b < n.nodeValue.length) n.splitText(b);
            var mid = a > 0 ? n.splitText(a) : n;
            var m = document.createElement('mark');
            m.className = 'hl'; m.dataset.g = g;
            mid.parentNode.insertBefore(m, mid); m.appendChild(mid);
            out.push(m);
        });
        return out;
    }
    function marksIn(range) { return [].slice.call(document.querySelectorAll('mark.hl')).filter(function (m) { return range.intersectsNode(m); }); }

    function refreshNotes() {
        var heads = [].slice.call(document.querySelectorAll('mark.hl.has-note.head')), box = notesWrap.querySelector('.t-notes-list');
        notesWrap.querySelector('.c').textContent = heads.length;
        box.innerHTML = heads.length ? '' : '<div class="empty">Select text, then choose Notes to add one.</div>';
        heads.forEach(function (mk) {
            var it = document.createElement('div');
            it.className = 'item';
            it.innerHTML = '<b></b><span></span>';
            it.querySelector('b').textContent = groupText(mk);
            it.querySelector('span').textContent = mk.dataset.note || 'No note yet';
            it.addEventListener('click', function () {
                var pane = mk.closest('.t-pane');
                if (pane && pane !== panes[current]) show(panes.indexOf(pane));
                mk.scrollIntoView({ block: 'center' });
                notesWrap.classList.remove('open');
                setTimeout(function () { openPopup(mk); }, 50);
            });
            box.appendChild(it);
        });
    }
    function openPopup(mk) {
        closeCtx();
        mk = head(mk);
        activeMark = mk;
        popup.querySelector('.sel').textContent = groupText(mk);
        popup.querySelector('textarea').value = mk.dataset.note || '';
        var r = mk.getBoundingClientRect();
        popup.style.left = Math.max(12, Math.min(r.left, innerWidth - 332)) + 'px';
        popup.style.top = (r.bottom + 230 > innerHeight ? Math.max(12, r.top - 230) : r.bottom + 8) + 'px';
        popup.classList.remove('open'); void popup.offsetWidth; popup.classList.add('open');
        popup.querySelector('textarea').focus();
    }
    function closePopup() {
        popup.classList.remove('open'); activeMark = null; refreshNotes();
    }
    popup.querySelector('.x').addEventListener('click', closePopup);
    popup.querySelector('.save').addEventListener('click', function () {
        var v = popup.querySelector('textarea').value.trim();
        if (v) activeMark.dataset.note = v; else delete activeMark.dataset.note;
        closePopup();
    });
    popup.querySelector('textarea').addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) popup.querySelector('.save').click();
        if (e.key === 'Escape') closePopup();
    });
    // Remove = drop the note, keep nothing highlighted for that text
    popup.querySelector('.del').addEventListener('click', function () { group(activeMark).forEach(unwrap); activeMark = null; closePopup(); });
    document.addEventListener('mousedown', function (e) {
        if (popup.classList.contains('open') && !popup.contains(e.target) && !(e.target.closest && e.target.closest('mark.hl'))) {
            if (activeMark) { var v = popup.querySelector('textarea').value.trim(); if (v) activeMark.dataset.note = v; }
            closePopup();
        }
    });

    /* menu */
    function openCtx(x, y) {
        ctx.querySelector('[data-act="clear"]').hidden = !marksIn(ctxRange).length;
        ctx.querySelector('[data-act="all"]').hidden = !document.querySelector('mark.hl');
        ctx.querySelector('hr').hidden = ctx.querySelector('[data-act="all"]').hidden;
        ctx.classList.add('open');
        var w = ctx.offsetWidth, h = ctx.offsetHeight;
        ctx.style.left = Math.max(8, Math.min(x + 6, innerWidth - w - 8)) + 'px';
        ctx.style.top = (y + 10 + h > innerHeight ? Math.max(8, y - h - 10) : y + 10) + 'px';
    }
    function closeCtx() { ctx.classList.remove('open'); ctxRange = null; }
    function selectionRange() {
        var sel = window.getSelection();
        if (!sel.rangeCount || sel.isCollapsed || !sel.toString().trim()) return null;
        var r = sel.getRangeAt(0), host = r.commonAncestorContainer;
        host = host.nodeType === 1 ? host : host.parentNode;
        if (!tabsRoot.contains(host) || host.closest('input, textarea, select, .t-tablist')) return null;
        return r.cloneRange();
    }
    document.addEventListener('mouseup', function (e) {
        if (e.button !== 0 || ctx.contains(e.target) || popup.contains(e.target)) return;
        if (e.target.closest && e.target.closest('input, textarea, select')) return;
        setTimeout(function () {
            var r = selectionRange();
            if (r) { ctxRange = r; openCtx(e.clientX, e.clientY); return; }
            var mk = e.target.closest && e.target.closest('mark.hl');
            if (mk && mk.classList.contains('has-note')) { openPopup(mk); return; }
            if (mk) {   // plain highlight clicked: offer Notes / Clear for it
                var g = group(mk), rr = document.createRange();
                rr.setStartBefore(g[0]); rr.setEndAfter(g[g.length - 1]);
                ctxRange = rr; openCtx(e.clientX, e.clientY); return;
            }
            closeCtx();
        }, 0);
    });
    document.addEventListener('contextmenu', function (e) {
        var r = selectionRange();
        if (!r) return;
        e.preventDefault();
        ctxRange = r; openCtx(e.clientX, e.clientY);
    });
    document.addEventListener('mousedown', function (e) { if (!ctx.contains(e.target)) ctx.classList.remove('open'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeCtx(); });
    tabsRoot.addEventListener('scroll', closeCtx, true);

    ctx.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b || !ctxRange) return;
        var act = b.dataset.act, r = ctxRange, sel = window.getSelection();
        if (act === 'hl' || act === 'note') {
            var existing = marksIn(r);
            var made = wrapRange(r);
            // a whole existing highlight selected again -> reuse it
            if (!made.length && existing.length) made = group(existing[0]);
            sel.removeAllRanges();
            closeCtx();
            if (!made.length) return;
            if (act === 'note') {
                // make all pieces one group so the note belongs to the whole selection
                var g = made[0].dataset.g;
                made.forEach(function (m) { m.dataset.g = g; m.classList.add('has-note'); });
                made[0].classList.add('head');
                openPopup(made[0]);
            }
            refreshNotes();
            return;
        }
        if (act === 'clear') {
            var gs = {};
            marksIn(r).forEach(function (m) { gs[m.dataset.g || Math.random()] = m; });
            Object.keys(gs).forEach(function (k) { group(gs[k]).forEach(unwrap); });
        }
        if (act === 'all') {
            document.querySelectorAll('mark.hl').forEach(unwrap);
        }
        sel.removeAllRanges();
        closeCtx(); closePopup();
    });

    /* ---------- "Choose TWO" questions: at most two ticks ---------- */
    document.querySelectorAll('.mc-q[data-max]').forEach(function (q) {
        q.addEventListener('change', function (e) {
            var on = q.querySelectorAll('input:checked');
            if (on.length > +q.dataset.max) e.target.checked = false;
        });
    });

    /* ---------- Writing word counters ---------- */
    document.querySelectorAll('.w-area').forEach(function (ta) {
        var out = ta.parentNode.querySelector('.w-count');
        function upd() { var n = ta.value.trim() ? ta.value.trim().split(/\s+/).length : 0; if (out) out.textContent = 'Words: ' + n; }
        ta.addEventListener('input', upd); upd();
    });

    /* ---------- Drag & drop matching (.dnd-match) ---------- */
    var drag = null;
    function boxAt(x, y) {
        var best = null, bd = 90;
        document.querySelectorAll('.t-pane.active .dnd-box').forEach(function (b) {
            var r = b.getBoundingClientRect();
            var dx = Math.max(r.left - x, 0, x - r.right), dy = Math.max(r.top - y, 0, y - r.bottom), d = Math.hypot(dx, dy);
            if (d < bd) { bd = d; best = b; }
        });
        return best;
    }
    function syncChips(scope) {
        var used = [].map.call(scope.querySelectorAll('.dnd-box input'), function (i) { return i.value; });
        scope.querySelectorAll('.dnd-chip').forEach(function (c) { c.classList.toggle('used', used.indexOf(c.dataset.value) > -1); });
    }
    function setBox(box, val) {
        box.querySelector('input').value = val || '';
        box.querySelector('.lbl').textContent = val || box.dataset.ph || '';
        box.classList.toggle('filled', !!val);
    }
    document.querySelectorAll('.dnd-box').forEach(function (b) {
        b.dataset.ph = b.querySelector('.lbl').textContent;
    });
    document.addEventListener('pointerdown', function (e) {
        var src = e.target.closest('.dnd-chip:not(.used), .dnd-box.filled');
        if (!src) return;
        e.preventDefault();
        var val = src.classList.contains('dnd-chip') ? src.dataset.value : src.querySelector('input').value;
        var r = src.getBoundingClientRect();
        var g = document.createElement('div');
        g.className = 'dnd-chip dnd-ghost'; g.textContent = val;
        document.body.appendChild(g);
        drag = { val: val, from: src.classList.contains('dnd-box') ? src : null, g: g, ox: e.clientX - r.left, oy: e.clientY - r.top, scope: src.closest('.dnd-match'), x: e.clientX, y: e.clientY };
        if (drag.from) setBox(drag.from, '');
        move(e);
    });
    function move(e) {
        if (!drag) return;
        drag.x = e.clientX; drag.y = e.clientY;
        drag.g.style.left = (e.clientX - drag.ox) + 'px';
        drag.g.style.top = (e.clientY - drag.oy) + 'px';
        document.querySelectorAll('.dnd-box.hover').forEach(function (b) { b.classList.remove('hover'); });
        var b = boxAt(e.clientX, e.clientY); if (b) b.classList.add('hover');
    }
    document.addEventListener('pointermove', move);
    setInterval(function () { // auto-scroll at the edges of the scroll box
        if (!drag) return;
        var sc = drag.scope.closest('.t-questions, .t-passage'); if (!sc) return;
        var r = sc.getBoundingClientRect();
        if (drag.y < r.top + 40) sc.scrollTop -= 12; else if (drag.y > r.bottom - 40) sc.scrollTop += 12;
    }, 16);
    document.addEventListener('pointerup', function (e) {
        if (!drag) return;
        var d = drag; drag = null;
        document.querySelectorAll('.dnd-box.hover').forEach(function (b) { b.classList.remove('hover'); });
        var box = boxAt(e.clientX, e.clientY);
        if (box) {
            var prev = box.querySelector('input').value;
            if (prev && d.from) setBox(d.from, prev); // swap
            var r = box.getBoundingClientRect();
            d.g.classList.add('glide');
            d.g.style.left = r.left + 'px'; d.g.style.top = r.top + 'px'; d.g.style.opacity = '0';
            setTimeout(function () { setBox(box, d.val); syncChips(d.scope); d.g.remove(); }, 180);
        } else { d.g.remove(); }
        syncChips(d.scope);
    });
});
</script>


<script>
/* Drag the handle between passage and questions to resize them (kept for this browser). */
document.addEventListener('DOMContentLoaded', function () {
    var KEY = 't-split', MIN = 20, MAX = 80, pct = 50;
    try { var v = parseFloat(localStorage.getItem(KEY)); if (v >= MIN && v <= MAX) pct = v; } catch (e) {}
    function apply() { document.querySelectorAll('.t-pane:not(.single)').forEach(function (p) { p.style.setProperty('--split', 'calc(' + pct + '% - 8px)'); }); }
    document.querySelectorAll('.t-pane:not(.single)').forEach(function (pane) {
        var q = pane.querySelector(':scope > .t-questions'), a = pane.querySelector(':scope > .t-passage');
        if (!q || !a) return;
        var h = document.createElement('div');
        h.className = 't-resizer'; h.title = 'Drag to resize'; h.setAttribute('role', 'separator');
        pane.insertBefore(h, q);
        h.addEventListener('pointerdown', function (e) {
            e.preventDefault(); h.setPointerCapture(e.pointerId); h.classList.add('dragging'); document.body.classList.add('t-resizing');
            function move(ev) {
                var r = pane.getBoundingClientRect();
                pct = Math.min(MAX, Math.max(MIN, (ev.clientX - r.left) / r.width * 100)); apply();
            }
            function up() {
                h.classList.remove('dragging'); document.body.classList.remove('t-resizing');
                h.removeEventListener('pointermove', move); h.removeEventListener('pointerup', up); h.removeEventListener('pointercancel', up);
                try { localStorage.setItem(KEY, pct); } catch (e) {}
            }
            h.addEventListener('pointermove', move); h.addEventListener('pointerup', up); h.addEventListener('pointercancel', up);
        });
        h.addEventListener('dblclick', function () { pct = 50; apply(); try { localStorage.setItem(KEY, pct); } catch (e) {} });
    });
    apply();
});
</script>

@include('front_end.layout.image-zoom')
