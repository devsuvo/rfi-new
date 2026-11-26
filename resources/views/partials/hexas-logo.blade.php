{{--
    Hexa's Zindabazar logo (PNG). Dark mode shows the white-letter version.
    Usage: @include('partials.hexas-logo', ['size' => 40])  -- size 40 = the header logo (54px tall)
--}}
@once
<style>
    .hx-logo-img { display: inline-flex; vertical-align: middle; }
    .hx-logo-img img { display: block; width: auto; }
    .hx-logo-img img.hx-dark { display: none !important; }
    :root[data-theme="dark"] .hx-logo-img img.hx-light { display: none !important; }
    :root[data-theme="dark"] .hx-logo-img img.hx-dark { display: block !important; }
</style>
@endonce
@php($h = round(($size ?? 40) * 1.35))
<span class="hx-logo-img" aria-label="Hexa's Zindabazar">
    <img class="hx-light" src="{{ asset('images/new-logo.png') }}" alt="Hexa's Zindabazar" style="height: {{ $h }}px">
    <img class="hx-dark" src="{{ asset('images/new-logo-dark.png') }}" alt="" style="height: {{ $h }}px">
</span>
