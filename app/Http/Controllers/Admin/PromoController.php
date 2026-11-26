<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoPopup;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.promos', [
            'promos' => PromoPopup::latest()->get(),
            'edit' => PromoPopup::find($request->query('edit')),
        ]);
    }

    public function store(Request $request)
    {
        PromoPopup::create($this->data($request));

        return back()->with('ok', 'Popup created.');
    }

    public function update(Request $request, PromoPopup $promo)
    {
        $promo->update($this->data($request));

        return redirect()->route('admin.promos.index')->with('ok', 'Popup updated.');
    }

    public function toggle(PromoPopup $promo)
    {
        $promo->update(['active' => ! $promo->active]);

        return back();
    }

    public function destroy(PromoPopup $promo)
    {
        $promo->delete();

        return back()->with('ok', 'Popup deleted.');
    }

    private function data(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:150',
            'body' => 'nullable|string|max:2000',
            'image_url' => 'nullable|url|max:500',
            'button_text' => 'nullable|string|max:60',
            'button_url' => 'nullable|string|max:500',
        ]) + ['active' => $request->boolean('active')];
    }
}
