{{-- + / − control pinned to the top-right of each passage box that has pictures. --}}
<style>
    .iz { position: sticky; top: 0; float: right; margin: -10px -12px 0 8px; z-index: 5; display: flex; flex-direction: column; background: #fff; border: 1px solid var(--t-line); border-radius: 12px; box-shadow: var(--shadow-sm); overflow: hidden; }
    .iz button { width: 30px; height: 28px; border: 0; background: #fff; color: var(--t-tone); font-size: 18px; font-weight: 700; line-height: 1; }
    .iz button + button { border-top: 1px solid var(--t-line); }
    .iz button:hover { background: var(--t-soft); }
    .iz-img { max-width: 100%; height: auto; transition: width .2s; pointer-events: none; display: block; }
    .pin-wrap { position: relative; }
    .pin-wrap.iz-img { pointer-events: auto; }
    .pin-wrap img, .pin-wrap svg { width: 100%; height: auto; display: block; }
    .pin-wrap img { pointer-events: none; }
    .pin-wrap svg.iz-img { pointer-events: auto; }
    .pin-wrap foreignObject { overflow: visible; }
    .pin-wrap .img-pin { display: block; width: calc(100% - 4px) !important; min-width: 0 !important; height: calc(100% - 4px) !important; margin: 2px !important; padding: 0 6px; font-size: 14px; line-height: 1; background: #fff; box-sizing: border-box !important; border-radius: 6px; }
    .pin-wrap .img-pin-abs { position: absolute; transform: translateY(-50%); width: auto; height: 26px; }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.t-passage, .w-chart, .q-figure').forEach(function (box) {
        var imgs = [].filter.call(box.querySelectorAll('img, .pin-wrap > svg'), function (i) {
            return box.classList.contains('q-figure') || !i.closest('.q-figure');
        });
        if (!imgs.length || box.querySelector('.iz')) return;
        var scale = 100;
        imgs.forEach(function (i) { i.classList.add('iz-img'); i.style.width = '100%'; });
        // a picture with answer boxes on it zooms as a whole, so the boxes stay over their blanks
        imgs = imgs.map(function (i) {
            var w = i.parentElement;
            return w.classList.contains('pin-wrap') ? (w.classList.add('iz-img'), w) : i;
        });
        var c = document.createElement('div');
        c.className = 'iz';
        c.innerHTML = '<button type="button" aria-label="Zoom in">+</button><button type="button" aria-label="Zoom out">−</button>';
        box.insertBefore(c, box.firstChild);
        function set(v) {
            scale = Math.max(50, Math.min(200, v));
            imgs.forEach(function (i) { i.style.width = scale + '%'; i.style.maxWidth = scale > 100 ? 'none' : '100%'; });
        }
        c.children[0].addEventListener('click', function () { set(scale + 20); });
        c.children[1].addEventListener('click', function () { set(scale - 20); });
    });
});
</script>
