<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PromoCode;
use Illuminate\Support\Carbon;

class AdminPromoCodeController extends Controller
{
    public function index()
    {
        $promoCodes = PromoCode::latest()->paginate(20);
        return view('admin.promo_codes.index', compact('promoCodes'));
    }

    public function create()
    {
        return view('admin.promo_codes.create');
    }

    public function store(PromoCode $promoCode)
    {

        $promoCode->validate([
            'code' => 'required|string|unique:promo_codes,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',

            'code' => 'required|string|unique:promo_codes,code,' . $promoCode->id,
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $data = $promoCode->only(['code', 'description', 'type', 'value', 'max_uses']);
        $data['active'] = $promoCode->boolean('active', true);
        $data['expires_at'] = $promoCode->expires_at ? Carbon::parse($promoCode->expires_at) : null;

        $promoCode->update($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promocode updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return redirect()->route('admin.promo-codes.index')->with('success', 'Promocode deleted.');
    }
}
