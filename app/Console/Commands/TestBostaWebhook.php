<?php

namespace App\Console\Commands;

use App\Services\BostaWebhookService;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class TestBostaWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bosta:test-webhook
                            {tracking_number : The tracking number to test}
                            {--status=delivered : The status to simulate}
                            {--delivery-id= : The BOSTA delivery ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test BOSTA webhook processing with simulated data';

    /**
     * Execute the console command.
     */
    public function handle(BostaWebhookService $webhookService): int
    {
        $trackingNumber = $this->argument('tracking_number');
        $status = $this->option('status');
        $deliveryId = $this->option('delivery-id');

        $this->info("Testing BOSTA webhook for tracking number: {$trackingNumber}");
        $this->info("Simulated status: {$status}");

        // Create simulated webhook payload
        $payload = [
            'trackingNumber' => $trackingNumber,
            '_id' => $deliveryId ?? 'test-delivery-id-' . uniqid(),
            'state' => $status,
            'type' => 'delivery:' . str_replace(' ', '_', strtolower($status)),
            'timestamp' => now()->toISOString(),
            'message' => "Test webhook event - {$status}",
            'location' => 'Cairo Hub',
            'hub' => 'Cairo Main',
            'test' => true,
        ];

        // Create a mock request
        $request = Request::create('/webhooks/bosta', 'POST', $payload);

        // Add test signature header (if webhook secret is configured)
        $webhookSecret = config('services.bosta.webhook_secret');
        if ($webhookSecret) {
            $signature = hash_hmac('sha256', json_encode($payload), $webhookSecret);
            $request->headers->set('X-Bosta-Signature', $signature);
        }

        $this->line('');
        $this->line('Webhook Payload:');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT));
        $this->line('');

        // Process the webhook
        $this->info('Processing webhook...');
        $result = $webhookService->processWebhook($request);

        $this->line('');

        if ($result['success']) {
            $this->info('✓ Webhook processed successfully!');

            if ($result['shipment']) {
                $shipment = $result['shipment'];

                $this->line('');
                $this->table(
                    ['Field', 'Value'],
                    [
                        ['Shipment ID', $shipment->id],
                        ['Tracking Number', $shipment->tracking_number],
                        ['Status', $shipment->status],
                        ['Order ID', $shipment->order_id],
                        ['Order Number', $shipment->order->order_number ?? 'N/A'],
                        ['Order Status', $shipment->order->status ?? 'N/A'],
                        ['Updated At', $shipment->updated_at->format('Y-m-d H:i:s')],
                    ]
                );

                // Show tracking history
                if ($shipment->tracking_history) {
                    $this->line('');
                    $this->info('Tracking History:');
                    foreach ($shipment->tracking_history as $index => $event) {
                        $this->line(sprintf(
                            '  %d. [%s] %s - %s',
                            $index + 1,
                            $event['timestamp'] ?? 'N/A',
                            $event['status'] ?? 'Update',
                            $event['message'] ?? 'No message'
                        ));
                    }
                }
            }

            return self::SUCCESS;
        } else {
            $this->error('✗ Webhook processing failed!');
            $this->error('Error: ' . $result['message']);

            return self::FAILURE;
        }
    }
}
