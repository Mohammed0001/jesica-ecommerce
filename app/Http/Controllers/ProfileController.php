<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

use App\Models\Address;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Store a new address for the user.
     */
    public function storeAddress(Request $request): RedirectResponse
    {
        $request->validate([
            'type' => 'nullable|string|in:shipping,billing',
            'company' => 'nullable|string|max:255',
            'address_line_1' => 'required|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state_province' => 'required|string|min:3|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'sometimes|boolean',
        ]);

        $user = $request->user();

        // If is_default is set, clear other defaults
        if ($request->boolean('is_default')) {
            Address::where('user_id', $user->id)->update(['is_default' => false]);
        }

        // Split user's name for first_name and last_name
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0] ?? '';
        $lastName = $nameParts[1] ?? '';

        $address = Address::create(array_merge($request->only([
            'type','company','address_line_1','address_line_2','city','state_province','postal_code','country'
        ]), [
            'user_id' => $user->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'is_default' => $request->boolean('is_default'),
        ]));

        return Redirect::route('profile.edit')->with('status', 'address-added');
    }

    /**
     * Delete an address belonging to the user.
     */
    public function destroyAddress(Request $request, Address $address): RedirectResponse
    {
        $user = $request->user();

        if ($address->user_id !== $user->id) {
            abort(403);
        }

        $address->delete();

        return Redirect::route('profile.edit')->with('status', 'address-deleted');
    }
}
