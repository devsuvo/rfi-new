{{-- Profile avatar menu (Dashboard, FAQ, Logout). --}}
<div class="user-menu">
    <button type="button" class="user-btn" aria-haspopup="true" aria-expanded="false">
        <span class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'Student', 0, 1)) }}</span>
        <span class="material-icons-outlined">expand_more</span>
    </button>
    <div class="user-drop" role="menu">
        <div class="user-name">{{ auth()->user()->name ?? 'Student' }}@if (auth()->user()?->batch)<small style="display:block;font-weight:500;color:#5E6A85;font-size:12px">Batch {{ auth()->user()->batch }}</small>@endif</div>
        @if (auth()->user()?->isStaff())
            <a href="{{ route('admin.choose') }}" role="menuitem"><span class="material-icons-outlined">dashboard</span>{{ auth()->user()->isAdmin() ? 'Admin' : 'Teacher' }} Dashboard</a>
        @elseif (auth()->check())
            <a href="{{ route('my.results') }}" role="menuitem"><span class="material-icons-outlined">insights</span>My Results</a>
        @endif
        <a href="#" role="menuitem"><span class="material-icons-outlined">help_outline</span>FAQ</a>
        @auth<a href="{{ route('logout') }}" role="menuitem" class="logout"><span class="material-icons-outlined">logout</span>Logout</a>@else<a href="{{ route('login') }}" role="menuitem"><span class="material-icons-outlined">login</span>Login</a>@endauth
    </div>
</div>
<style>
    .user-menu { position: relative; }
    .user-btn { display: flex; align-items: center; gap: 4px; border: 1px solid rgba(10,22,64,.1); background: #fff; border-radius: 999px; padding: 4px 8px 4px 4px; cursor: pointer; color: #5E6A85; box-shadow: 0 1px 2px rgba(10,22,64,.05); transition: box-shadow .2s, border-color .2s; }
    .user-btn:hover, .user-menu.open .user-btn { border-color: rgba(10,22,64,.2); box-shadow: 0 4px 14px rgba(10,22,64,.1); }
    .user-btn > .material-icons-outlined { transition: transform .25s; }
    .user-menu.open .user-btn > .material-icons-outlined { transform: rotate(180deg); }
    .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #13235E, #0A1640); color: #F6D88A; display: grid; place-items: center; font-weight: 700; font-size: 14px; box-shadow: inset 0 0 0 1.5px rgba(232,184,74,.5); }
    .user-drop { position: absolute; right: 0; top: calc(100% + 10px); min-width: 230px; background: rgba(255,255,255,.96); backdrop-filter: blur(16px); border: 1px solid rgba(10,22,64,.08); border-radius: 18px; box-shadow: 0 2px 4px rgba(10,22,64,.04), 0 24px 48px -12px rgba(10,22,64,.22); padding: 8px; z-index: 60; opacity: 0; visibility: hidden; transform: translateY(-6px) scale(.98); transform-origin: top right; transition: opacity .2s, transform .25s cubic-bezier(.2,.8,.2,1), visibility .2s; }
    .user-menu.open .user-drop { opacity: 1; visibility: visible; transform: none; }
    .user-name { padding: 10px 12px 12px; font-weight: 700; font-size: 14px; color: #0A1640; border-bottom: 1px solid #E6E9F2; margin-bottom: 6px; }
    .user-drop a { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 10px; font-size: 14px; font-weight: 500; color: #0B1433; transition: background .15s; }
    .user-drop a .material-icons-outlined { font-size: 19px; color: #5E6A85; }
    .user-drop a:hover { background: #F3F4F9; }
    .user-drop a.logout:hover, .user-drop a.logout:hover .material-icons-outlined { color: #E2263C; background: #FDE9EC; }
</style>
<script>
    document.addEventListener('click', function (e) {
        document.querySelectorAll('.user-menu').forEach(function (m) {
            var btn = m.querySelector('.user-btn');
            var open = btn.contains(e.target) ? !m.classList.contains('open') : m.contains(e.target) && m.classList.contains('open');
            m.classList.toggle('open', open);
            btn.setAttribute('aria-expanded', open);
        });
    });
</script>
