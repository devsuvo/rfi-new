@extends('admin.layout')
@section('title', 'Promo Popups')

@section('content')
<div class="page-head"><div><h1>Promo Popups</h1><div class="sub">The newest active popup is shown on the student site once per visit.</div></div></div>

<div class="grid" style="grid-template-columns:minmax(0,1fr) minmax(0,1.3fr);align-items:start">
    @php($p = $edit ?? new \App\Models\PromoPopup(['active' => true]))
    <form class="card" method="POST" action="{{ $edit ? route('admin.promos.update', $edit) : route('admin.promos.store') }}">
        @csrf @if ($edit) @method('PUT') @endif
        <h3 style="margin:0 0 14px">{{ $edit ? 'Edit popup' : 'New popup' }}</h3>
        <div class="grid">
            <div><label class="f">Title *</label><input name="title" class="input" value="{{ old('title', $p->title) }}" required></div>
            <div><label class="f">Message</label><textarea name="body" rows="4" class="input">{{ old('body', $p->body) }}</textarea></div>
            <div><label class="f">Image URL</label><input name="image_url" type="url" class="input" value="{{ old('image_url', $p->image_url) }}" placeholder="https://…"></div>
            <div class="grid g2">
                <div><label class="f">Button text</label><input name="button_text" class="input" value="{{ old('button_text', $p->button_text) }}" placeholder="Enroll now"></div>
                <div><label class="f">Button link</label><input name="button_url" class="input" value="{{ old('button_url', $p->button_url) }}" placeholder="https://…"></div>
            </div>
            <label class="muted" style="display:flex;gap:8px;align-items:center"><input type="checkbox" name="active" value="1" @checked(old('active', $p->active))> Active</label>
        </div>
        <div class="row-acts" style="justify-content:flex-start;margin-top:16px">
            <button class="btn primary">{{ $edit ? 'Update' : 'Create' }}</button>
            @if ($edit)<a class="btn ghost" href="{{ route('admin.promos.index') }}">Cancel</a>@endif
        </div>
    </form>

    <div class="card">
        <div class="tbl-wrap"><table class="t">
            <tr><th>Popup</th><th>Status</th><th></th></tr>
            @forelse ($promos as $promo)
                <tr>
                    <td><b>{{ $promo->title }}</b><div class="muted" style="font-size:12px">{{ \Illuminate\Support\Str::limit($promo->body, 70) }}</div></td>
                    <td>
                        <form method="POST" action="{{ route('admin.promos.toggle', $promo) }}">@csrf
                            <button class="badge {{ $promo->active ? 'green' : 'red' }}" style="border:0;cursor:pointer">{{ $promo->active ? 'Active' : 'Off' }}</button>
                        </form>
                    </td>
                    <td><div class="row-acts">
                        <a class="btn sm" href="?edit={{ $promo->id }}">Edit</a>
                        <form method="POST" action="{{ route('admin.promos.destroy', $promo) }}" data-confirm="Delete this popup?">@csrf @method('DELETE')<button class="btn danger sm">Delete</button></form>
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="3" class="empty">No popups yet.</td></tr>
            @endforelse
        </table></div>
    </div>
</div>
@endsection
