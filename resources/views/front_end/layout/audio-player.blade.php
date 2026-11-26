{{-- Custom audio player. Replaces every audio[controls] on the page. --}}
<style>
    .ap { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid var(--t-line); border-radius: 999px; padding: 8px 20px 8px 8px; box-shadow: var(--shadow-sm); margin: 0 0 18px; position: relative; }
    .ap-play { width: 44px; height: 44px; border-radius: 50%; border: 0; background: var(--t-tone); color: #fff; display: grid; place-items: center; flex: none; box-shadow: 0 8px 18px -6px var(--t-tone); transition: transform .2s; }
    .ap-play:hover { transform: scale(1.06); }
    .ap-time { font-size: 14px; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .ap-time b { font-weight: 700; }
    .ap-time span { color: var(--t-muted); }
    .ap-seek { flex: 1; height: 6px; border-radius: 3px; background: var(--t-line); position: relative; cursor: pointer; }
    .ap-seek i { position: absolute; left: 0; top: 0; bottom: 0; border-radius: 3px; background: var(--t-tone); width: 0; }
    .ap-seek i::after { content: ''; position: absolute; right: -7px; top: 50%; width: 14px; height: 14px; margin-top: -7px; border-radius: 50%; background: #fff; border: 3px solid var(--t-tone); box-shadow: 0 2px 6px rgba(10,22,64,.2); }
    .ap-vol { position: relative; }
    .ap-vol > button { border: 0; background: none; color: var(--t-ink); display: grid; place-items: center; }
    .ap-vol-pop { position: absolute; bottom: calc(100% + 14px); left: 50%; transform: translateX(-50%); background: #fff; border: 1px solid var(--t-line); border-radius: 12px; padding: 12px 8px; box-shadow: 0 12px 28px rgba(11,31,92,.14); display: none; }
    .ap-vol.open .ap-vol-pop { display: block; }
    .ap-vol-pop input { writing-mode: vertical-lr; direction: rtl; height: 100px; width: 20px; accent-color: var(--t-tone); }
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    function fmt(t) { t = Math.floor(t || 0); return Math.floor(t / 60) + ':' + ('0' + t % 60).slice(-2); }
    document.querySelectorAll('audio[controls]').forEach(function (a) {
        a.removeAttribute('controls');
        var p = document.createElement('div');
        p.className = 'ap';
        p.innerHTML = '<button type="button" class="ap-play" aria-label="Play"><span class="material-icons-outlined">play_arrow</span></button>' +
            '<div class="ap-time"><b class="c">0:00</b> <span>/ <span class="d">0:00</span></span></div>' +
            '<div class="ap-seek"><i></i></div>' +
            '<div class="ap-vol"><button type="button" aria-label="Volume"><span class="material-icons-outlined">volume_up</span></button>' +
            '<div class="ap-vol-pop"><input type="range" min="0" max="1" step="0.05" value="1" aria-label="Volume"></div></div>';
        a.parentNode.insertBefore(p, a);
        p.appendChild(a);
        var btn = p.querySelector('.ap-play'), bar = p.querySelector('.ap-seek'), vol = p.querySelector('.ap-vol');
        btn.addEventListener('click', function () { a.paused ? a.play() : a.pause(); });
        a.addEventListener('play', function () { btn.firstChild.textContent = 'pause'; });
        a.addEventListener('pause', function () { btn.firstChild.textContent = 'play_arrow'; });
        a.addEventListener('loadedmetadata', function () { p.querySelector('.d').textContent = fmt(a.duration); });
        a.addEventListener('timeupdate', function () {
            p.querySelector('.c').textContent = fmt(a.currentTime);
            bar.firstChild.style.width = (a.duration ? a.currentTime / a.duration * 100 : 0) + '%';
        });
        bar.addEventListener('click', function (e) {
            var r = bar.getBoundingClientRect();
            if (a.duration) a.currentTime = (e.clientX - r.left) / r.width * a.duration;
        });
        vol.querySelector('button').addEventListener('click', function () { vol.classList.toggle('open'); });
        document.addEventListener('click', function (e) { if (!vol.contains(e.target)) vol.classList.remove('open'); });
        vol.querySelector('input').addEventListener('input', function () {
            a.volume = this.value;
            vol.querySelector('.material-icons-outlined').textContent = a.volume == 0 ? 'volume_off' : a.volume < .5 ? 'volume_down' : 'volume_up';
        });
    });
});
</script>
