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

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:promo_codes,code',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['expires_at'] = $data['expires_at'] ? Carbon::parse($data['expires_at']) : null;

        PromoCode::create($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promocode created.');
    }

    public function edit(PromoCode $promoCode)
    {
        return view('admin.promo_codes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        $data = $request->validate([
            'code' => 'required|string|unique:promo_codes,code,' . $promoCode->id,
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'active' => 'boolean',
        ]);

        $data['active'] = $request->boolean('active', true);
        $data['expires_at'] = $data['expires_at'] ? Carbon::parse($data['expires_at']) : null;

        $promoCode->update($data);

        return redirect()->route('admin.promo-codes.index')->with('success', 'Promocode updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        $promoCode->delete();
        return redirect()->route('admin.promo-codes.index')->with('success', 'Promocode deleted.');
    }
}
