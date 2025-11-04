<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Pax8Service;
use Illuminate\Support\Facades\Log;

class Pax8WebhookController extends Controller
{
    public function handle(Request $request, Pax8Service $service)
    {
        try {
            $service->handleWebhook($request->all());
            return response()->json(['message' => 'Webhook processed']);
        } catch (\Throwable $e) {
            Log::error('Pax8 webhook error', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Error processing webhook'], 500);
        }
    }
}
