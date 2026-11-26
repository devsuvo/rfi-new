@include('partials.theme-anim')
<style>
    .theme-fab { position: fixed; top: 16px; right: 16px; z-index: 50; width: 44px; height: 44px; border-radius: 50%; display: grid; place-items: center; overflow: hidden;
        border: 1px solid var(--line); background: var(--panel); color: var(--ink); cursor: pointer; box-shadow: 0 6px 18px -8px rgba(0,0,0,.3); transition: transform .25s, box-shadow .25s; }
    .theme-fab:hover { transform: scale(1.06); box-shadow: 0 10px 22px -10px rgba(0,0,0,.4); }
    .theme-fab:active { transform: scale(.94); }
    .theme-fab .material-icons-outlined { grid-area: 1 / 1; font-size: 22px; transition: transform .5s cubic-bezier(.3, 1.4, .5, 1), opacity .3s; }
    .theme-fab .ic-moon { color: #5B6CFF; }
    .theme-fab .ic-sun { color: #F5B83D; transform: rotate(-120deg) scale(.3); opacity: 0; }
    :root[data-theme="dark"] .theme-fab .ic-sun { transform: none; opacity: 1; }
    :root[data-theme="dark"] .theme-fab .ic-moon { transform: rotate(120deg) scale(.3); opacity: 0; }
</style>
<button type="button" class="theme-fab" title="Toggle dark / light mode" aria-label="Toggle dark / light mode"
    onclick="var b=this;hxSwitchTheme(b,function(){var r=document.documentElement,d=r.dataset.theme==='dark';if(d)delete r.dataset.theme;else r.dataset.theme='dark';try{localStorage.setItem('admin-theme',d?'light':'dark')}catch(e){}})">
    <span class="material-icons-outlined ic-moon">dark_mode</span><span class="material-icons-outlined ic-sun">light_mode</span>
</button>
