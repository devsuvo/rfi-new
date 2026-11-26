{{--
    Dropdown answer picker shown over select.para-select / select.sel-answer.
    The first (hidden) option holds the question number; it shows in the box until a letter is picked.
--}}
<style>
    .lp { position: relative; display: inline-block; vertical-align: middle; }
    .lp select { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }
    .lp-box { min-width: 68px; height: 32px; border: 1.5px solid var(--t-line); border-radius: 10px; background: #FAFBFD; cursor: pointer; transition: border-color .2s, box-shadow .2s; display: inline-flex; align-items: center; justify-content: space-between; gap: 6px; padding: 0 6px 0 10px; font-size: 14px; color: var(--t-muted); }
    .lp-box .material-icons-outlined { font-size: 18px; }
    .lp.filled .lp-box { color: var(--t-ink); font-weight: 600; border-color: var(--t-tone); }
    .lp.open .lp-box { border-color: var(--t-tone); box-shadow: 0 0 0 3px var(--t-soft); }
    .lp-list { position: absolute; left: 0; top: calc(100% + 4px); min-width: 100%; max-height: 260px; overflow: auto; background: #fff; border: 1px solid var(--t-line); border-radius: 14px; box-shadow: var(--shadow-lg); z-index: 40; display: none; padding: 4px; }
    .lp.open .lp-list { display: block; }
    .lp-list .pin { position: sticky; top: -4px; background: #fff; text-align: center; font-size: 12px; color: var(--t-muted); padding: 4px 0; border-bottom: 1px solid var(--t-line); }
    .lp-list button { display: block; width: 100%; border: 0; background: none; padding: 6px 10px; font-size: 13px; font-weight: 700; border-radius: 8px; text-align: center; }
    .lp-list button:hover { background: var(--t-soft); }
    .lp-list button.on { background: var(--t-soft); color: var(--t-tone); }

    /* CD IELTS style drag & drop for "List of Headings" and "Sentence endings" (the select stays the real answer field) */
    .hd-src li.hd-opt { display: flex; align-items: flex-start; gap: 8px; padding: 8px 12px !important; margin: 0 0 6px; background: #fff; border: 1px solid #CFD6E2; border-radius: 6px; cursor: move; user-select: none; touch-action: none; box-shadow: 0 1px 3px rgba(11,27,63,.08); transition: border-color .15s, box-shadow .15s, transform .15s, opacity .2s; }
    .hd-src li.hd-opt:hover { border-color: var(--t-tone); box-shadow: 0 6px 14px -6px rgba(11,27,63,.25); transform: translateY(-1px); }
    .hd-src li.hd-opt.picked { border-color: var(--t-tone); box-shadow: 0 0 0 3px var(--t-soft); }
    .hd-src li.hd-opt.used { display: none; }
    .hd-src li.hd-opt b { flex: none; }
    .hd-src li.hd-opt .hd-txt { flex: 1; }
    .hd-grip { flex: none; margin-left: auto; font-size: 20px !important; color: #9AA4B8; transition: color .15s; }
    .hd-src li.hd-opt:hover .hd-grip, .hd-src li.hd-opt.picked .hd-grip { color: var(--t-tone); }
    .hd-zone.filled .hd-grip { position: absolute; right: 30px; top: 50%; transform: translateY(-50%); }
    .hd-zone.filled { padding-right: 56px; }
    .hd-src.wide ul { display: block; }
    .hd-hint { font-size: 12.5px; color: var(--t-muted); margin: -4px 0 10px; display: flex; align-items: center; gap: 6px; }
    .hd-hint .material-icons-outlined { font-size: 16px; }
    .hd-zone { position: relative; display: flex; align-items: center; gap: 8px; min-height: 40px; padding: 6px 34px 6px 10px; border: 1.5px dashed #AAB4C5; border-radius: 6px; background: #FAFBFD; color: var(--t-muted); font-size: 14px; transition: border-color .15s, background .15s, transform .15s; }
    .hd-zone select { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }
    .hd-zone .hd-num { flex: none; min-width: 26px; height: 24px; display: inline-grid; place-items: center; border: 1.5px solid var(--t-tone); border-radius: 5px; background: #fff; color: #0B1B3F; font-weight: 800; font-size: 13px; }
    .hd-zone .hd-val { flex: 1; }
    .hd-zone .hd-val:empty::before { content: 'Drop answer here'; font-style: italic; opacity: .7; }
    .hd-zone.filled { border-style: solid; border-color: var(--t-tone); background: #fff; color: #0B1B3F; font-weight: 600; cursor: move; touch-action: none; }
    .hd-zone.filled .hd-val b { color: var(--t-tone); margin-right: 6px; }
    .hd-zone.hover { border-color: var(--t-tone); background: var(--t-soft); transform: scale(1.01); }
    .hd-zone.armed { border-color: var(--t-tone); }
    .hd-zone .hd-x { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); display: none; width: 22px; height: 22px; border: 0; border-radius: 50%; background: #EEF1F6; color: #5B6B86; cursor: pointer; font-size: 15px; line-height: 22px; padding: 0; }
    .hd-zone.filled .hd-x { display: block; }
    .hd-zone.locked { pointer-events: none; }
    .hd-zone:has(select.ans-ok) { border-color: #16A34A; background: #E7F6EC; }
    .hd-zone:has(select.ans-bad) { border-color: #DC2626; background: #FDECEC; }
    .hd-zone:has(select[disabled]) .hd-x { display: none; }
    .q-row .hd-zone { flex: 1 1 260px; min-width: 220px; }
    .q-row:has(.hd-zone) { flex-wrap: wrap; }
    .hd-slot { margin: 14px 0 8px; }
    .q-row.hd-moved { display: none; }
    .hd-ghost { position: fixed; z-index: 300; pointer-events: none; max-width: 360px; padding: 8px 12px; background: #fff; border: 1.5px solid var(--t-tone); border-radius: 6px; font-size: 14px; font-weight: 600; color: #0B1B3F; box-shadow: 0 16px 32px rgba(10,22,64,.25); transform: rotate(-1.5deg); }
    .hd-ghost b { color: var(--t-tone); margin-right: 6px; }
    body.hd-dragging, body.hd-dragging * { cursor: move !important; user-select: none !important; }

    /* Summary completion with a word list: drag a word chip into a gap in the text */
    .sm-src { display: none; }
    .sm-gap { position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 100px; height: 30px; margin: 2px 2px; padding: 0 12px; vertical-align: middle; border: 1.5px solid #CFD6E2; border-radius: 6px; background: #fff; color: var(--t-muted); font-size: 12.5px; font-weight: 600; line-height: 1; transition: border-color .15s, background .15s; }
    .sm-gap select { position: absolute; opacity: 0; pointer-events: none; width: 1px; height: 1px; }
    .sm-gap.filled { color: #0B1B3F; font-size: 14px; border-color: var(--t-tone); cursor: move; touch-action: none; }
    .sm-gap.hover, .sm-gap.armed { border-color: var(--t-tone); background: var(--t-soft); }
    .sm-gap:has(select.ans-ok) { border-color: #16A34A; background: #E7F6EC; }
    .sm-gap:has(select.ans-bad) { border-color: #DC2626; background: #FDECEC; }
    .sm-bank { display: flex; flex-wrap: wrap; gap: 12px; margin: 22px 0 10px; }
    .sm-chip { padding: 8px 14px; background: #fff; border: 1px solid #CFD6E2; border-radius: 6px; font-size: 14px; font-weight: 600; color: #0B1B3F; cursor: move; user-select: none; touch-action: none; box-shadow: 0 1px 3px rgba(11,27,63,.08); transition: border-color .15s, box-shadow .15s, transform .15s; }
    .sm-chip:hover { border-color: var(--t-tone); transform: translateY(-1px); }
    .sm-chip.picked { border-color: var(--t-tone); box-shadow: 0 0 0 3px var(--t-soft); }
    .sm-chip.used { visibility: hidden; }

    /* List of Headings: plain heading chips (no letters) and small dashed drop boxes showing the question number */
    .opt-box.hd-k-head { background: none; border: 0; box-shadow: none; padding: 0; }
    .opt-box.hd-k-head .hd-hint { display: none; }
    .opt-box.hd-k-head .opt-title { margin: 0 0 12px; font-weight: 800; color: #0B1B3F; }
    .opt-box.hd-k-head ul { display: flex !important; flex-direction: column; align-items: flex-start; gap: 9px; margin: 0; padding: 0; list-style: none; }
    .hd-src.hd-k-head li.hd-opt { display: inline-flex; width: auto; max-width: 100%; margin: 0; padding: 7px 14px !important; border: 1px solid #CFD6E2; border-radius: 3px; background: #fff; font-size: 14px; font-weight: 500; color: #0B1B3F; box-shadow: none; cursor: move; }
    .hd-src.hd-k-head li.hd-opt:hover { border-color: var(--t-tone); box-shadow: 0 4px 12px -6px rgba(11,27,63,.3); }
    .hd-src.hd-k-head li.hd-opt.picked { border-color: var(--t-tone); box-shadow: 0 0 0 3px var(--t-soft); }
    .hd-src.hd-k-head li.hd-opt b, .hd-src.hd-k-head li.hd-opt .hd-grip { display: none; }
    .hd-src.hd-k-head li.hd-opt.used { display: none; }
    .hd-zone.hd-k-head { display: inline-flex; justify-content: center; width: 260px; max-width: 100%; min-height: 30px; padding: 4px 30px 4px 12px; border: 1px dashed #A8B1C2; border-radius: 4px; background: #fff; color: #6B7280; font-size: 13px; font-weight: 500; }
    .hd-zone.hd-k-head .hd-num { min-width: 0; height: auto; border: 0; background: none; color: #6B7280; font-size: 13px; font-weight: 600; }
    .hd-zone.hd-k-head .hd-val { flex: 0 1 auto; text-align: center; }
    .hd-zone.hd-k-head .hd-val:empty { display: none; }
    .hd-zone.hd-k-head .hd-grip { display: none !important; }
    .hd-zone.hd-k-head.filled { width: auto; min-width: 260px; justify-content: flex-start; padding-right: 34px; border: 1px solid var(--t-tone); background: var(--t-soft); color: #0B1B3F; font-weight: 600; }
    .hd-zone.hd-k-head.filled .hd-num { color: var(--t-tone); margin-right: 2px; }
    .hd-zone.hd-k-head.filled .hd-num::after { content: '.'; }
    .hd-zone.hd-k-head .hd-val b { display: none; }
    .hd-zone.hd-k-head.hover, .hd-zone.hd-k-head.armed { border-color: var(--t-tone); background: var(--t-soft); }
    .hd-slot .hd-zone.hd-k-head { margin: 0; }
    .q-row .hd-zone.hd-k-head { flex: 0 0 260px; min-width: 0; }
    .q-row .hd-zone.hd-k-head.filled { flex: 0 1 auto; min-width: 260px; }
    .hd-zone.hd-k-head .hd-num { display: inline-flex; place-items: initial; align-items: center; }
    .hd-zone.hd-k-head.filled { min-height: 32px; }
    .hd-ghost.hd-k-head { border-radius: 3px; font-weight: 500; }
    .hd-ghost.hd-k-head b { display: none; }
    /* matching people / writers (A–D …): the instruction names the letters, so show them */
    .hd-src.hd-k-match.hd-k-head li.hd-opt b, .hd-zone.hd-k-match.hd-k-head .hd-val b, .hd-ghost.hd-k-match b { display: inline; color: var(--t-tone); font-weight: 800; margin-right: 6px; }
    /* Matching names / writers: chips in rows, each one can be used for several questions */
    .opt-box.hd-k-match ul { flex-direction: row !important; flex-wrap: wrap; }
</style>
<script>
/* Drag & drop for List of Headings / Sentence endings. Runs before the dropdown picker so those selects are left alone. */
function hdInit() {
    var esc = function (s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; };
    var groups = [];
    document.querySelectorAll('.t-questions .opt-box').forEach(function (box) {
        var instr = box.previousElementSibling;
        while (instr && !instr.classList.contains('t-instr') && !/^H4$/.test(instr.tagName)) instr = instr.previousElementSibling;
        var label = ((box.querySelector('.opt-title') || {}).textContent || '') + ' ' + (instr && instr.classList.contains('t-instr') ? instr.textContent : '');
        var kind = /heading/i.test(label) ? 'head' : /ending/i.test(label) ? 'end' : 'match'; // match = names, writers … (one option may answer several questions)
        if (!kind) return;
        var opts = {};
        box.querySelectorAll('li').forEach(function (li) {
            var b = li.querySelector('b'); if (!b) return;
            var v = b.textContent.trim();
            opts[v] = { li: li, text: li.textContent.slice(b.textContent.length).trim() };
        });
        var rows = [], n = box.nextElementSibling;
        while (n && n.classList.contains('q-row')) { if (n.querySelector('select.sel-answer')) rows.push(n); n = n.nextElementSibling; }
        if (!rows.length || !Object.keys(opts).length) return;
        var fits = rows.every(function (r) { return [].slice.call(r.querySelector('select').options, 1).every(function (o) { return opts[o.value]; }); });
        if (!fits) return;

        var g = { reuse: kind === 'match', box: box, opts: opts, zones: [], pane: box.closest('.t-pane') };
        box.classList.add('hd-src', 'hd-k-' + kind); if (kind === 'match') box.classList.add('hd-k-head');
        box.insertAdjacentHTML('afterbegin', '<div class="hd-hint"><span class="material-icons-outlined">drag_indicator</span>Drag an answer into a box' + (kind === 'head' ? ' in the passage' : '') + ' (or tap it, then tap the box).</div>');
        Object.keys(opts).forEach(function (v) {
            var li = opts[v].li, b = li.querySelector('b'), txt = document.createElement('span');
            txt.className = 'hd-txt';
            while (b.nextSibling) txt.appendChild(b.nextSibling);
            li.appendChild(txt);
            li.insertAdjacentHTML('beforeend', '<span class="material-icons-outlined hd-grip" aria-hidden="true">drag_indicator</span>');
            li.classList.add('hd-opt'); li.dataset.v = v; li._hd = g;
        });

        rows.forEach(function (row) {
            var s = row.querySelector('select.sel-answer');
            s.classList.add('dd-answer');
            var num = s.options[0].text;
            var zone = document.createElement('div');
            zone.className = 'hd-zone hd-k-' + kind + (kind === 'match' ? ' hd-k-head' : '');
            zone.innerHTML = '<span class="hd-num"></span><span class="hd-val"></span><span class="material-icons-outlined hd-grip" aria-hidden="true" hidden>drag_indicator</span><button type="button" class="hd-x" title="Remove" aria-label="Remove">&times;</button>';
            zone.querySelector('.hd-num').textContent = num;
            zone._hd = g; zone._sel = s;
            zone.appendChild(s);
            var placed = false;
            if (kind === 'head') {
                var m = row.querySelector('.q-text').textContent.trim().match(/\b([A-Z])$/);
                if (m && g.pane) {
                    var para = [].find.call(g.pane.querySelectorAll('.t-passage .para-label'), function (l) { return l.textContent.trim() === m[1]; });
                    if (para) {
                        var slot = document.createElement('div'); slot.className = 'hd-slot';
                        slot.appendChild(zone);
                        para.closest('p').before(slot);
                        row.classList.add('hd-moved');
                        placed = true;
                    }
                }
            }
            if (!placed) {
                // the row already ends with its own label (e.g. "Theme 1"), so drop the duplicate number
                var qt = row.querySelector('.q-text');
                if (qt && new RegExp('\\b' + num + '$').test(qt.textContent.trim())) zone.querySelector('.hd-num').remove();
                row.appendChild(zone);
            }
            s.addEventListener('change', function () { sync(g); });
            g.zones.push(zone);
        });
        groups.push(g);
        sync(g);
    });
    if (!groups.length) return;

    function sync(g) {
        var used = {};
        g.zones.forEach(function (z) {
            var v = z._sel.value, o = g.opts[v];
            z.classList.toggle('filled', !!o);
            z.querySelector('.hd-val').innerHTML = o ? '<b>' + esc(v) + '</b>' + esc(o.text) : '';
            z.querySelector('.hd-grip').hidden = !o;
            if (o) used[v] = true;
        });
        Object.keys(g.opts).forEach(function (v) { g.opts[v].li.classList.toggle('used', !g.reuse && !!used[v]); });
    }
    function locked(z) { return z._sel.disabled || z._sel.readOnly === true || !!z.closest('.t-pane.timeup'); }
    function setZone(z, v) {
        if (locked(z)) return;
        z._sel.value = v || '';
        z._sel.dispatchEvent(new Event('change', { bubbles: true }));
    }
    function zoneAt(g, x, y) {
        var best = null, bd = 60;
        g.zones.forEach(function (z) {
            if (locked(z)) return;
            var r = z.getBoundingClientRect(); if (!r.width) return;
            var d = Math.hypot(Math.max(r.left - x, 0, x - r.right), Math.max(r.top - y, 0, y - r.bottom));
            if (d < bd) { bd = d; best = z; }
        });
        return best;
    }
    function clearHover() { document.querySelectorAll('.hd-zone.hover').forEach(function (z) { z.classList.remove('hover'); }); }
    function unpick() { document.querySelectorAll('.hd-opt.picked').forEach(function (o) { o.classList.remove('picked'); }); document.querySelectorAll('.hd-zone.armed').forEach(function (z) { z.classList.remove('armed'); }); }

    var drag = null, picked = null;
    document.addEventListener('pointerdown', function (e) {
        if (e.button > 0) return;
        if (e.target.closest('.hd-x')) return;
        var opt = e.target.closest('.hd-opt:not(.used)'), zone = e.target.closest('.hd-zone.filled');
        var src = opt || zone; if (!src) return;
        var g = src._hd;
        if (zone && locked(zone)) return;
        if (opt && g.zones.every(locked)) return;
        e.preventDefault();
        drag = { g: g, v: opt ? opt.dataset.v : zone._sel.value, from: zone, opt: opt, sx: e.clientX, sy: e.clientY, moved: false, ghost: null };
    });
    document.addEventListener('pointermove', function (e) {
        if (!drag) return;
        if (!drag.moved) {
            if (Math.hypot(e.clientX - drag.sx, e.clientY - drag.sy) < 6) return;
            drag.moved = true; unpick(); picked = null;
            var gh = document.createElement('div'); gh.className = 'hd-ghost';
            gh.innerHTML = '<b>' + esc(drag.v) + '</b>' + esc(drag.g.opts[drag.v].text);
            if (drag.g.box.classList.contains('hd-k-head')) gh.classList.add('hd-k-head');
            if (drag.g.box.classList.contains('hd-k-match')) gh.classList.add('hd-k-match');
            document.body.appendChild(gh); drag.ghost = gh;
            document.body.classList.add('hd-dragging');
        }
        drag.x = e.clientX; drag.y = e.clientY;
        drag.ghost.style.left = (e.clientX + 12) + 'px'; drag.ghost.style.top = (e.clientY + 10) + 'px';
        clearHover();
        var z = zoneAt(drag.g, e.clientX, e.clientY); if (z) z.classList.add('hover');
    });
    setInterval(function () { // auto-scroll the column under the pointer while dragging
        if (!drag || !drag.moved || !drag.g.pane) return;
        [].forEach.call(drag.g.pane.querySelectorAll('.t-passage, .t-questions'), function (sc) {
            var r = sc.getBoundingClientRect();
            if (drag.x < r.left || drag.x > r.right) return;
            if (drag.y < r.top + 40) sc.scrollTop -= 12; else if (drag.y > r.bottom - 40) sc.scrollTop += 12;
        });
    }, 16);
    document.addEventListener('pointerup', function (e) {
        if (!drag) return;
        var d = drag; drag = null;
        clearHover(); document.body.classList.remove('hd-dragging');
        if (!d.moved) { // a tap: pick an option, or with an option picked tap a box
            if (d.opt) {
                var on = !d.opt.classList.contains('picked'); unpick();
                if (on) { d.opt.classList.add('picked'); picked = d.opt; d.g.zones.forEach(function (z) { if (!locked(z)) z.classList.add('armed'); }); } else picked = null;
            } else if (d.from && picked && picked._hd === d.g) {
                setZone(d.from, picked.dataset.v); unpick(); picked = null;
            }
            return;
        }
        d.ghost.remove();
        var z = zoneAt(d.g, e.clientX, e.clientY);
        if (z) {
            if (z === d.from) return;
            var prev = z._sel.value;
            setZone(z, d.v);
            if (d.from) setZone(d.from, prev); // swap (or empty) the box it came from
        } else if (d.from) {
            setZone(d.from, ''); // dropped outside: back to the list
        }
    });
    // double-click a filled box: the heading goes back to its place in the list
    document.addEventListener('dblclick', function (e) {
        var z = e.target.closest('.hd-zone.filled');
        if (!z || e.target.closest('.hd-x')) return;
        setZone(z, ''); unpick(); picked = null;
        var s = window.getSelection && window.getSelection(); if (s) s.removeAllRanges();
    });
    document.addEventListener('click', function (e) {
        var x = e.target.closest('.hd-x');
        if (x) { setZone(x.closest('.hd-zone'), ''); return; }
        var z = e.target.closest('.hd-zone:not(.filled)');
        if (z && picked && picked._hd === z._hd) { setZone(z, picked.dataset.v); unpick(); picked = null; return; }
        if (!e.target.closest('.hd-opt, .hd-zone')) { unpick(); picked = null; }
    });
}

/* Summary completion "using the list of words, A–L": the letter list becomes word chips under the text,
   every gap becomes a box showing its question number. The hidden select keeps the letter as the answer. */
function smInit() {
    var groups = [];
    document.querySelectorAll('.t-questions .opt-box:not(.hd-src)').forEach(function (box) {
        var opts = {};
        box.querySelectorAll('li').forEach(function (li) {
            var b = li.querySelector('b'); if (!b) return;
            opts[b.textContent.trim()] = li.textContent.slice(b.textContent.length).trim();
        });
        if (!Object.keys(opts).length) return;
        var sels = [], last = null, n = box.nextElementSibling;
        while (n && !/^H[1-3]$/.test(n.tagName) && !n.classList.contains('opt-box') && !n.classList.contains('q-row') && !n.classList.contains('mc-q')) {
            var s = n.querySelectorAll('select.sel-answer');
            if (s.length) { sels = sels.concat([].slice.call(s)); last = n; }
            n = n.nextElementSibling;
        }
        if (!sels.length) return;
        var fits = sels.every(function (s) { return [].slice.call(s.options, 1).every(function (o) { return opts[o.value] !== undefined; }); });
        if (!fits) return;

        var g = { opts: opts, gaps: [], chips: {}, pane: box.closest('.t-pane') };
        box.classList.add('sm-src');
        var bank = document.createElement('div'); bank.className = 'sm-bank';
        Object.keys(opts).forEach(function (v) {
            var c = document.createElement('div');
            c.className = 'sm-chip'; c.textContent = opts[v]; c.dataset.v = v; c._sm = g;
            bank.appendChild(c); g.chips[v] = c;
        });
        last.after(bank);
        sels.forEach(function (s) {
            s.classList.add('dd-answer');
            var gap = document.createElement('span');
            gap.className = 'sm-gap'; gap._sm = g; gap._sel = s; gap._num = s.options[0].text;
            s.parentNode.insertBefore(gap, s);
            gap.appendChild(s);
            s.addEventListener('change', function () { sync(g); });
            g.gaps.push(gap);
        });
        groups.push(g); sync(g);
    });
    if (!groups.length) return;
    setInterval(function () { groups.forEach(sync); }, 700); // picks up answers restored from a saved attempt

    function sync(g) {
        var used = {};
        g.gaps.forEach(function (z) {
            var v = z._sel.value, w = g.opts[v];
            z.classList.toggle('filled', w !== undefined);
            var t = w !== undefined ? w : z._num;
            if (z.firstChild.nodeType !== 3) z.insertBefore(document.createTextNode(''), z.firstChild);
            if (z.firstChild.nodeValue !== t) z.firstChild.nodeValue = t;
            if (w !== undefined) used[v] = true;
        });
        Object.keys(g.chips).forEach(function (v) { g.chips[v].classList.toggle('used', !!used[v]); });
    }
    function locked(z) { return z._sel.disabled || z._sel.readOnly === true || !!z.closest('.t-pane.timeup'); }
    function setGap(z, v) { if (locked(z)) return; z._sel.value = v || ''; z._sel.dispatchEvent(new Event('change', { bubbles: true })); }
    function gapAt(g, x, y) {
        var best = null, bd = 40;
        g.gaps.forEach(function (z) {
            if (locked(z)) return;
            var r = z.getBoundingClientRect(); if (!r.width) return;
            var d = Math.hypot(Math.max(r.left - x, 0, x - r.right), Math.max(r.top - y, 0, y - r.bottom));
            if (d < bd) { bd = d; best = z; }
        });
        return best;
    }
    function clear() { document.querySelectorAll('.sm-gap.hover, .sm-gap.armed').forEach(function (z) { z.classList.remove('hover', 'armed'); }); document.querySelectorAll('.sm-chip.picked').forEach(function (c) { c.classList.remove('picked'); }); }

    var drag = null, picked = null;
    document.addEventListener('pointerdown', function (e) {
        if (e.button > 0) return;
        var chip = e.target.closest('.sm-chip:not(.used)'), gap = e.target.closest('.sm-gap');
        if (!chip && !gap) return;
        if (gap && (locked(gap) || !gap.classList.contains('filled'))) return;
        var g = (chip || gap)._sm;
        if (chip && g.gaps.every(locked)) return;
        e.preventDefault();
        drag = { g: g, v: chip ? chip.dataset.v : gap._sel.value, chip: chip, from: gap, sx: e.clientX, sy: e.clientY, moved: false };
    });
    document.addEventListener('pointermove', function (e) {
        if (!drag) return;
        if (!drag.moved) {
            if (Math.hypot(e.clientX - drag.sx, e.clientY - drag.sy) < 6) return;
            drag.moved = true; clear(); picked = null;
            drag.ghost = document.createElement('div'); drag.ghost.className = 'hd-ghost';
            drag.ghost.textContent = drag.g.opts[drag.v];
            document.body.appendChild(drag.ghost); document.body.classList.add('hd-dragging');
        }
        drag.ghost.style.left = (e.clientX + 12) + 'px'; drag.ghost.style.top = (e.clientY + 10) + 'px';
        document.querySelectorAll('.sm-gap.hover').forEach(function (z) { z.classList.remove('hover'); });
        var z = gapAt(drag.g, e.clientX, e.clientY); if (z) z.classList.add('hover');
    });
    document.addEventListener('pointerup', function (e) {
        if (!drag) return;
        var d = drag; drag = null;
        document.body.classList.remove('hd-dragging');
        if (!d.moved) {
            if (d.chip) { // tap a word, then tap a gap
                var on = !d.chip.classList.contains('picked'); clear(); picked = null;
                if (on) { d.chip.classList.add('picked'); picked = d.chip; d.g.gaps.forEach(function (z) { if (!locked(z)) z.classList.add('armed'); }); }
            } else if (d.from) { // tap a filled gap: put the word back, or swap in the picked one
                if (picked && picked._sm === d.g) { setGap(d.from, picked.dataset.v); } else { setGap(d.from, ''); }
                clear(); picked = null;
            }
            return;
        }
        d.ghost.remove(); clear();
        var z = gapAt(d.g, e.clientX, e.clientY);
        if (z) { if (z === d.from) return; var prev = z._sel.value; setGap(z, d.v); if (d.from) setGap(d.from, prev); }
        else if (d.from) setGap(d.from, '');
    });
    document.addEventListener('click', function (e) {
        var z = e.target.closest('.sm-gap:not(.filled)');
        if (z && picked && picked._sm === z._sm) { setGap(z, picked.dataset.v); clear(); picked = null; return; }
        if (!e.target.closest('.sm-chip, .sm-gap')) { clear(); picked = null; }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    try { hdInit(); } catch (err) { console.error(err); }
    try { smInit(); } catch (err) { console.error(err); }
    document.querySelectorAll('select.sel-answer:not(.dd-answer), select.para-select').forEach(function (s) {
        var num = s.options[0].text;
        var w = document.createElement('span');
        w.className = 'lp';
        s.parentNode.insertBefore(w, s);
        w.appendChild(s);
        w.insertAdjacentHTML('beforeend', '<button type="button" class="lp-box"><span class="v"></span><span class="material-icons-outlined">expand_more</span></button><div class="lp-list"><div class="pin"></div></div>');
        var list = w.querySelector('.lp-list');
        list.querySelector('.pin').textContent = num;
        [].slice.call(s.options, 1).forEach(function (o) {
            var b = document.createElement('button');
            b.type = 'button'; b.textContent = o.text; b.dataset.v = o.value;
            b.addEventListener('click', function () { s.value = o.value; s.dispatchEvent(new Event('change')); w.classList.remove('open'); });
            list.appendChild(b);
        });
        function sync() {
            var picked = s.selectedIndex > 0;
            w.querySelector('.v').textContent = picked ? s.options[s.selectedIndex].text : num;
            w.classList.toggle('filled', picked);
            list.querySelectorAll('button').forEach(function (b) { b.classList.toggle('on', b.dataset.v === s.value && picked); });
        }
        s.addEventListener('change', sync); sync();
        w.querySelector('.lp-box').addEventListener('click', function () {
            document.querySelectorAll('.lp.open').forEach(function (o) { if (o !== w) o.classList.remove('open'); });
            w.classList.toggle('open');
        });
    });
    document.addEventListener('click', function (e) { if (!e.target.closest('.lp')) document.querySelectorAll('.lp.open').forEach(function (o) { o.classList.remove('open'); }); });
});
</script>
