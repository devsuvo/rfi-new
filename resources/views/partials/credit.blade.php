{{-- Site credit line. The year updates by itself every January. Usage: @include('partials.credit') --}}
<div class="hx-credit">
    <span>&copy; {{ date('Y') }} Hexa's Zindabazar. All rights reserved.</span>
    <span class="hx-dot" aria-hidden="true">•</span>
    <span>Developed by <b>Hexa's Zindabazar</b> <span class="hx-sep">|</span> Digital Media Center</span>
</div>
@once
<style>
    .hx-credit { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 4px 10px;
        padding: 16px 12px; font-size: 12.5px; line-height: 1.5; text-align: center; color: inherit; opacity: .72; }
    .hx-credit b { font-weight: 700; }
    .hx-credit .hx-sep, .hx-credit .hx-dot { opacity: .5; }
    @media (max-width: 560px) { .hx-credit .hx-dot { display: none; } .hx-credit > span { width: 100%; } }
</style>
@endonce
