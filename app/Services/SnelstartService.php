<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SnelstartService
{
    protected function baseUrl(): string
    {
        return 'https://b2bapi.snelstart.nl/v2';
    }

    protected function token(): string
    {
        return env('SNELSTART_API_TOKEN');
    }

    protected function snelstartRequest()
    {
        return Http::withToken($this->token())
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }

    /* =======================================================
       ===============   COMPANIES (RELATIES)  ================
       ======================================================= */

    public function addSnelstartCompany(array $request)
    {
        try {
            $url = $this->baseUrl() . '/relaties';
            $response = $this->snelstartRequest()->post($url, $request);

            if ($response->successful()) {
                return response()->json(['message' => 'Company added successfully', 'data' => $response->json()]);
            }

            Log::warning('Failed to add company: ' . $response->body());
            return response()->json(['message' => 'Failed to add company', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error adding company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while adding company'], 500);
        }
    }

    public function updateSnelstartCompany(string $id, array $request)
    {
        try {
            $url = $this->baseUrl() . '/relaties/' . $id;
            $response = $this->snelstartRequest()->put($url, $request);

            if ($response->successful()) {
                return response()->json(['message' => 'Company updated successfully', 'data' => $response->json()]);
            }

            Log::warning('Failed to update company: ' . $response->body());
            return response()->json(['message' => 'Failed to update company', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error updating company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while updating company'], 500);
        }
    }

    public function deleteSnelstartCompany(string $id)
    {
        try {
            $url = $this->baseUrl() . '/relaties/' . $id;
            $response = $this->snelstartRequest()->delete($url);

            if ($response->successful()) {
                return response()->json(['message' => 'Company deleted successfully']);
            }

            Log::warning('Failed to delete company: ' . $response->body());
            return response()->json(['message' => 'Failed to delete company', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error deleting company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while deleting company'], 500);
        }
    }

    public function getEqualCompany(?string $btwNummer)
    {
        if (empty($btwNummer)) {
            return null;
        }

        try {
            $encoded = rawurlencode("btwNummer eq '{$btwNummer}'");
            $url = $this->baseUrl() . "/relaties?filter={$encoded}";

            $response = $this->snelstartRequest()->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Failed to fetch equal company for BTW {$btwNummer}: " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Error fetching equal company: ' . $e->getMessage());
            return null;
        }
    }

    /* =======================================================
       ==================   INVOICES  ========================
       ======================================================= */

    public function getEqualInvoice(?string $externalId)
    {
        if (empty($externalId)) {
            return null;
        }

        try {
            $encoded = rawurlencode("externId eq '{$externalId}'");
            $url = $this->baseUrl() . "/verkoopboekingen?filter={$encoded}";

            $response = $this->snelstartRequest()->get($url);

            if ($response->successful()) {
                return $response->json();
            }

            Log::warning("Failed to fetch equal invoice for externId {$externalId}: " . $response->body());
            return null;
        } catch (\Throwable $e) {
            Log::error('Error fetching equal invoice: ' . $e->getMessage());
            return null;
        }
    }

    public function addSnelstartInvoice(array $invoice)
    {
        try {
            $url = $this->baseUrl() . '/verkoopboekingen';
            $response = $this->snelstartRequest()->post($url, $invoice);

            if ($response->successful()) {
                return response()->json(['message' => 'Invoice added successfully', 'data' => $response->json()]);
            }

            Log::warning('Failed to add invoice: ' . $response->body());
            return response()->json(['message' => 'Failed to add invoice', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error adding invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while adding invoice'], 500);
        }
    }

    public function updateSnelstartInvoice(string $id, array $invoice)
    {
        try {
            $url = $this->baseUrl() . '/verkoopboekingen/' . $id;
            $response = $this->snelstartRequest()->put($url, $invoice);

            if ($response->successful()) {
                return response()->json(['message' => 'Invoice updated successfully', 'data' => $response->json()]);
            }

            Log::warning('Failed to update invoice: ' . $response->body());
            return response()->json(['message' => 'Failed to update invoice', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error updating invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while updating invoice'], 500);
        }
    }

    public function deleteSnelstartInvoice(string $id)
    {
        try {
            $url = $this->baseUrl() . '/verkoopboekingen/' . $id;
            $response = $this->snelstartRequest()->delete($url);

            if ($response->successful()) {
                return response()->json(['message' => 'Invoice deleted successfully']);
            }

            Log::warning('Failed to delete invoice: ' . $response->body());
            return response()->json(['message' => 'Failed to delete invoice', 'error' => $response->json()], $response->status());
        } catch (\Throwable $e) {
            Log::error('Error deleting invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while deleting invoice'], 500);
        }
    }
}
