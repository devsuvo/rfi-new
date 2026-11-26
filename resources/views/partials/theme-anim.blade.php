{{-- Animated light/dark switch: a circle grows out of the toggle button (View Transitions API); older browsers get a soft colour fade. --}}
<style>
    ::view-transition-old(root), ::view-transition-new(root) { animation: none; mix-blend-mode: normal; }
    ::view-transition-old(root) { z-index: 1; }
    ::view-transition-new(root) { z-index: 2; }
    html.theme-fade, html.theme-fade *, html.theme-fade *::before, html.theme-fade *::after {
        transition: background-color .45s ease, color .45s ease, border-color .45s ease, fill .45s ease, box-shadow .45s ease !important;
    }
</style>
<script>
window.hxSwitchTheme = function (btn, apply) {
    var root = document.documentElement;
    var reduce = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) { apply(); return; }
    if (!document.startViewTransition || !btn) {
        root.classList.add('theme-fade');
        apply();
        setTimeout(function () { root.classList.remove('theme-fade'); }, 500);
        return;
    }
    var r = btn.getBoundingClientRect(), x = r.left + r.width / 2, y = r.top + r.height / 2;
    var end = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));
    var t = document.startViewTransition(apply);
    t.ready.then(function () {
        root.animate(
            { clipPath: ['circle(0px at ' + x + 'px ' + y + 'px)', 'circle(' + end + 'px at ' + x + 'px ' + y + 'px)'] },
            { duration: 650, easing: 'cubic-bezier(.4, 0, .2, 1)', pseudoElement: '::view-transition-new(root)' }
        );
    }).catch(function () {});
};
</script>
