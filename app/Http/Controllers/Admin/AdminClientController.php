<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class AdminClientController extends Controller
{
    /**
     * Display a listing of clients (users)
     */
    public function index()
    {
        $clients = User::with(['orders'])
            ->withCount(['orders'])
            ->latest()
            ->paginate(20);

        return view('admin.clients.index', compact('clients'));
    }

    /**
     * Display the specified client
     */
    public function show(User $client)
    {
        $client->load(['orders.orderItems.product']);

        return view('admin.clients.show', compact('client'));
    }

    /**
     * Update the specified client
     */
    public function update(Request $request, User $client)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $client->id,
            'phone' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
        ]);

        $client->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'is_active' => $request->status === 'active',
        ]);

        return redirect()->route('admin.clients.show', $client)
            ->with('success', 'Client updated successfully.');
    }

    /**
     * Toggle a user between the AFFILIATE role and the CLIENT role so they
     * can (or can no longer) see the affiliate dashboard.
     */
    public function toggleAffiliate(User $client)
    {
        if ($client->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot be made affiliates.');
        }

        $affiliateRole = Role::where('name', 'AFFILIATE')->first();
        $clientRole = Role::where('name', 'CLIENT')->first();

        if (!$affiliateRole || !$clientRole) {
            return back()->with('error', 'Affiliate/Client roles are not set up. Run the role seeder first.');
        }

        if ($client->isAffiliate()) {
            $client->update(['role_id' => $clientRole->id]);
            $message = $client->name . ' is no longer an affiliate.';
        } else {
            $client->update(['role_id' => $affiliateRole->id]);
            $message = $client->name . ' can now sign in as an affiliate.';
        }

        return back()->with('success', $message);
    }
}
