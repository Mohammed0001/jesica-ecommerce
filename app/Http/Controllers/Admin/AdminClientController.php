<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
}
