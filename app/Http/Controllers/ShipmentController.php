<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Shipment;
use App\Services\BostaService;
use App\Services\BostaWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShipmentController extends Controller
{
    protected BostaService $bostaService;
    protected BostaWebhookService $webhookService;

    public function __construct(BostaService $bostaService, BostaWebhookService $webhookService)
    {
        $this->bostaService = $bostaService;
        $this->webhookService = $webhookService;
    }

    /**
     * Display a listing of shipments
     */
    public function index(Request $request)
    {
        $query = Shipment::with('order.user')->latest();

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by tracking number
        if ($request->filled('tracking_number')) {
            $query->where('tracking_number', 'like', '%' . $request->tracking_number . '%');
        }

        // Filter by order number
        if ($request->filled('order_number')) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('order_number', 'like', '%' . $request->order_number . '%');
            });
        }

        $shipments = $query->paginate(20);

        return view('admin.shipments.index', compact('shipments'));
    }

    /**
     * Show shipment details
     */
    public function show(Shipment $shipment)
    {
        $shipment->load('order.user', 'order.items.product');

        // Fetch latest tracking info
        $this->bostaService->updateShipmentFromTracking($shipment);
        $shipment->refresh();

        return view('admin.shipments.show', compact('shipment'));
    }

    /**
     * Create a new shipment for an order
     */
    public function create(Order $order)
    {
        if ($order->shipment) {
            return redirect()->back()->with('error', 'This order already has a shipment.');
        }

        $shipment = $this->bostaService->createShipment($order);

        if ($shipment) {
            return redirect()
                ->route('admin.shipments.show', $shipment)
                ->with('success', 'Shipment created successfully! Tracking Number: ' . $shipment->tracking_number);
        }

        return redirect()->back()->with('error', 'Failed to create shipment. Please check the logs.');
    }

    /**
     * Cancel a shipment
     */
    public function cancel(Shipment $shipment)
    {
        // Check if shipment has BOSTA delivery ID
        if (!$shipment->bosta_delivery_id) {
            return redirect()->route('admin.shipments.show', $shipment)
                ->with('error', 'This shipment does not have a BOSTA delivery ID and cannot be cancelled.');
        }

        // Check if shipment can be cancelled based on status
        if (!$shipment->canBeCancelled()) {
            $statusMessage = match($shipment->status) {
                'delivered' => 'This shipment has already been delivered and cannot be cancelled.',
                'cancelled' => 'This shipment is already cancelled.',
                'returned' => 'This shipment has been returned and cannot be cancelled.',
                default => 'This shipment cannot be cancelled in its current status.',
            };

            return redirect()->route('admin.shipments.show', $shipment)
                ->with('error', $statusMessage);
        }

        // Attempt to cancel the shipment
        $success = $this->bostaService->cancelShipment($shipment);

        if ($success) {
            // Reload the shipment to get updated status
            $shipment->refresh();

            $message = 'Shipment cancelled successfully.';

            // Check if it was a 404 scenario (not found in BOSTA)
            $lastEvent = $shipment->tracking_history ?
                collect($shipment->tracking_history)->last() : null;

            if ($lastEvent && str_contains($lastEvent['message'] ?? '', 'not found in BOSTA')) {
                $message = 'Shipment marked as cancelled. Note: The shipment was not found in BOSTA system (may have been already cancelled or removed).';
            }

            return redirect()->route('admin.shipments.show', $shipment)
                ->with('success', $message);
        }

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('error', 'Failed to cancel shipment with BOSTA. The shipment may not exist in their system or cannot be cancelled at this time. Please check the logs for more details.');
    }

    /**
     * Print AWB label
     */
    public function printLabel(Shipment $shipment)
    {
        $awbUrl = $this->bostaService->getAWBUrl($shipment);

        if (!$awbUrl) {
            return redirect()->route('admin.shipments.show', $shipment)->with('error', 'Unable to generate AWB label.');
        }

        // Redirect to BOSTA AWB URL for printing
        return redirect($awbUrl);
    }

    /**
     * Update tracking information
     */
    public function updateTracking(Shipment $shipment)
    {
        // Check if shipment has tracking number
        if (!$shipment->tracking_number) {
            return redirect()->route('admin.shipments.show', $shipment)
                ->with('error', 'This shipment does not have a tracking number and cannot be updated.');
        }

        // Check if shipment is in a valid state for tracking updates
        if ($shipment->status === 'pending') {
            return redirect()->route('admin.shipments.show', $shipment)
                ->with('warning', 'This shipment is still pending and may not have tracking information available yet.');
        }

        // Attempt to update tracking information
        $success = $this->bostaService->updateShipmentFromTracking($shipment);

        if ($success) {
            // Check if status changed
            $statusMessage = 'Tracking information updated successfully.';
            if ($shipment->wasChanged('status')) {
                $statusMessage .= ' Status updated to: ' . ucfirst(str_replace('_', ' ', $shipment->status));
            }

            return redirect()->route('admin.shipments.show', $shipment)
                ->with('success', $statusMessage);
        }

        return redirect()->route('admin.shipments.show', $shipment)
            ->with('error', 'Failed to update tracking information from BOSTA. Please check the logs or try again later.');
    }

    /**
     * Bulk request pickup
     */
    public function requestPickup(Request $request)
    {
        $request->validate([
            'shipment_ids' => 'required|array',
            'shipment_ids.*' => 'exists:shipments,id',
            'pickup_date' => 'nullable|date|after_or_equal:today',
        ]);

        $shipments = Shipment::whereIn('id', $request->shipment_ids)
            ->where('status', Shipment::STATUS_CREATED)
            ->get();

        if ($shipments->isEmpty()) {
            return redirect()->back()->with('error', 'No valid shipments selected for pickup.');
        }

        $deliveryIds = $shipments->pluck('bosta_delivery_id')->filter()->toArray();

        if (empty($deliveryIds)) {
            return redirect()->back()->with('error', 'Selected shipments do not have valid BOSTA delivery IDs.');
        }

        $result = $this->bostaService->requestPickup($deliveryIds, $request->pickup_date);

        if ($result) {
            return redirect()->back()->with('success', 'Pickup requested successfully for ' . count($deliveryIds) . ' shipments.');
        }

        return redirect()->back()->with('error', 'Failed to request pickup. Please try again.');
    }

    /**
     * Handle BOSTA webhook callbacks
     */
    public function webhook(Request $request)
    {
        // Process webhook using the dedicated service
        $result = $this->webhookService->processWebhook($request);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ], 200);
        }

        // Determine appropriate HTTP status code based on the error
        $statusCode = match ($result['message']) {
            'Invalid webhook signature' => 403,
            'Shipment not found' => 404,
            default => 400,
        };

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], $statusCode);
    }

    /**
     * Show customer tracking page
     */
    public function track(Request $request, $trackingNumber = null)
    {
        $trackingNumber = $trackingNumber ?? $request->tracking_number;

        if (!$trackingNumber) {
            return view('tracking.search');
        }

        $shipment = Shipment::where('tracking_number', $trackingNumber)
            ->with('order')
            ->first();

        if (!$shipment) {
            return view('tracking.search')->with('error', 'Tracking number not found.');
        }

        // Update tracking information
        $this->bostaService->updateShipmentFromTracking($shipment);
        $shipment->refresh();

        return view('tracking.show', compact('shipment'));
    }
}
