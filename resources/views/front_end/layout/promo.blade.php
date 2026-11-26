{{-- Latest active promo popup (managed in Admin → Promo Popups). Shown once per browser session. --}}
@php($promo = \App\Models\PromoPopup::where('active', true)->latest('updated_at')->first())
@if ($promo)
<div class="promo-pop" id="promoPop" data-key="promo-{{ $promo->id }}-{{ $promo->updated_at?->timestamp }}" hidden>
    <div class="promo-card" role="dialog" aria-modal="true" aria-label="{{ $promo->title }}">
        <button type="button" class="promo-x" aria-label="Close"><span class="material-icons-outlined">close</span></button>
        @if ($promo->image_url)<img src="{{ $promo->image_url }}" alt="">@endif
        <div class="promo-body">
            <h3>{{ $promo->title }}</h3>
            @if ($promo->body)<p>{!! nl2br(e($promo->body)) !!}</p>@endif
            @if ($promo->button_text && $promo->button_url)
                <a class="promo-btn" href="{{ $promo->button_url }}">{{ $promo->button_text }}</a>
            @endif
        </div>
    </div>
</div>
<style>
    .promo-pop { position: fixed; inset: 0; z-index: 200; display: grid; place-items: center; padding: 16px; background: rgba(10,22,64,.55); backdrop-filter: blur(4px); animation: rise .35s var(--ease) both; }
    .promo-pop[hidden] { display: none; }
    .promo-card { position: relative; width: min(440px, 100%); background: #fff; border-radius: 22px; overflow: hidden; box-shadow: var(--shadow-lg); }
    .promo-card img { display: block; width: 100%; max-height: 240px; object-fit: cover; }
    .promo-body { padding: 24px; }
    .promo-body h3 { margin: 0 0 8px; font-size: 22px; color: var(--hp-navy); }
    .promo-body p { margin: 0 0 18px; color: var(--t-muted); line-height: 1.6; font-size: 15px; }
    .promo-btn { display: inline-block; background: var(--hp-blue); color: #fff; font-weight: 700; padding: 12px 20px; border-radius: 12px; }
    .promo-x { position: absolute; top: 10px; right: 10px; width: 34px; height: 34px; border-radius: 50%; border: 0; background: rgba(255,255,255,.9); cursor: pointer; display: grid; place-items: center; box-shadow: var(--shadow-sm); }
</style>
<script>
(function () {
    var el = document.getElementById('promoPop'), key = el.dataset.key;
    try { if (sessionStorage.getItem(key)) return; } catch (e) {}
    el.hidden = false;
    function close() { el.hidden = true; try { sessionStorage.setItem(key, '1'); } catch (e) {} }
    el.querySelector('.promo-x').addEventListener('click', close);
    el.addEventListener('click', function (e) { if (e.target === el) close(); });
})();
</script>
@endif
