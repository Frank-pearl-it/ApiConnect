<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AutotaskService
{
    protected function autotaskRequest()
    {
        return Http::withHeaders([
            'ApiIntegrationCode' => env('AUTOTASK_API_CODE'),
            'UserName' => env('AUTOTASK_USERNAME'),
            'Secret' => env('AUTOTASK_SECRET'),
            'Content-Type' => 'application/json',
            'User-Agent' => 'Autotask-Laravel-Client'

        ])->withoutVerifying();
    }

    protected function unwrapItem(array $json): ?array
    {
        if (isset($json['item'])) {
            return $json['item'];
        }
        if (isset($json['items'][0])) {
            return $json['items'][0];
        }
        return null;
    }

    public function getAutoTaskCompany($id): ?array
    {
        try {
            Log::info('Fetching Autotask company with ID: ' . $id);

            $url = "https://webservices19.autotask.net/atservicesrest/v1.0/Companies/{$id}";
            $response = $this->autotaskRequest()->get($url);

            // Log raw response if not successful
            if (!$response->successful()) {
                Log::warning('Autotask company fetch failed', [
                    'id' => $id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers(),
                ]);
                return null;
            }

            $data = $this->unwrapItem($response->json());
            Log::info('Autotask company fetch success', ['id' => $id]);
            return $data;
        } catch (\Throwable $e) {
            Log::error('Autotask company request error', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }


    public function getAutoTaskInvoice($id): ?array
    {
        try {
            $url = "https://webservices19.autotask.net/atservicesrest/v1.0/Invoices/{$id}";
            $response = $this->autotaskRequest()->get($url);

            if ($response->successful()) {
                return $this->unwrapItem($response->json());
            }

            Log::warning("Autotask invoice fetch failed: {$response->status()} for ID {$id}");
            return null;
        } catch (\Throwable $e) {
            Log::error('Autotask invoice request error: ' . $e->getMessage());
            return null;
        }
    }
}
