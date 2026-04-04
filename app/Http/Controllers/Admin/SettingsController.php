<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use App\Models\SiteSetting;

class SettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // you may add admin gate middleware here if available
    }

    public function edit()
    {
        $delivery_fee = SiteSetting::get('delivery_fee', '15');
        $delivery_threshold = SiteSetting::get('delivery_threshold', '200');
        $tax_percentage = SiteSetting::get('tax_percentage', '14');
        $service_fee_percentage = SiteSetting::get('service_fee_percentage', '0');
        $hero_image = SiteSetting::get('hero_image', null);

        return view('admin.settings', compact(
            'delivery_fee', 'delivery_threshold', 'tax_percentage',
            'service_fee_percentage', 'hero_image'
        ));
    }

    public function update(Request $request)
    {
        $request->validate([
            'delivery_fee'          => 'required|numeric|min:0',
            'delivery_threshold'    => 'required|numeric|min:0',
            'tax_percentage'        => 'required|numeric|min:0',
            'service_fee_percentage'=> 'required|numeric|min:0',
        ]);

        SiteSetting::set('delivery_fee', $request->delivery_fee);
        SiteSetting::set('delivery_threshold', $request->delivery_threshold);
        SiteSetting::set('tax_percentage', $request->tax_percentage);
        SiteSetting::set('service_fee_percentage', $request->service_fee_percentage);

        return Redirect::back()->with('success', 'Settings updated');
    }

    /**
     * Upload and replace the home page hero image
     */
    public function uploadHero(Request $request)
    {
        $request->validate([
            'hero_image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        // Store the new hero image in the public disk under images/
        $file = $request->file('hero_image');
        $filename = 'hero-background.' . $file->getClientOriginalExtension();
        $file->move(public_path('images'), $filename);

        // Save the public URL path in site settings
        SiteSetting::set('hero_image', 'images/' . $filename);

        return Redirect::route('admin.settings.edit')
            ->with('success', 'Hero image updated successfully.');
    }

    /**
     * Reset hero image back to default
     */
    public function resetHero()
    {
        SiteSetting::set('hero_image', '');

        return Redirect::route('admin.settings.edit')
            ->with('success', 'Hero image reset to default.');
    }
}
