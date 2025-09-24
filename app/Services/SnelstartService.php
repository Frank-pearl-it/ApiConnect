<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
class SnelstartService
{
    public function getSnelStartToken()
    {
        return env('SNELSTART_API_TOKEN');
    }
    public function addSnelstartCompany($request)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/relaties';

        // Send the request to the API
        $response = Http::withToken($this->getSnelStartToken())->post($url, $request);

        // Handle the response
        if ($response->successful()) {
            return response()->json(['message' => 'Company added successfully']);
        }

        return response()->json(['message' => 'Failed to add company'], 500);
    }


    public function updateSnelstartCompany($id, $request)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/relaties/' . $id;

        // Send the request to the API
        $response = Http::withToken($this->getSnelStartToken())->put($url, $request);

        // Handle the response
        if ($response->successful()) {
            return response()->json(['message' => 'Company updated successfully']);
        }
        return response()->json(['message' => 'Failed to update company'], 500);
    }

    public function deleteSnelstartCompany($id)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/relaties/' . $id;

        // Send the request to the API
        $response = Http::withToken($this->getSnelStartToken())->delete($url);

        // Handle the response
        if ($response->successful()) {
            return response()->json(['message' => 'Company deleted successfully']);
        }

        return response()->json(['message' => 'Failed to delete company'], 500);
    }

    public function getEqualCompany($btwNummer)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/relaties?filter=btwNummer eq ' . $btwNummer;

        // Send the request to the API
        $response = Http::withToken($this->getSnelStartToken())->get($url);

        // Handle the response
        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function getEqualInvoice($externalId)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/verkoopboekingen?filter=externId eq ' . $externalId;

        // Send the request to the API
        $response = Http::withToken($this->getSnelStartToken())->get($url);

        // Handle the response
        if ($response->successful()) {
            return $response->json();
        }

        return null;
    }

    public function addSnelstartInvoice(array $invoice)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/verkoopboekingen';

        $response = Http::withToken($this->getSnelStartToken())
            ->post($url, $invoice);

        return $response->successful()
            ? response()->json(['message' => 'Invoice added successfully'])
            : response()->json(['message' => 'Failed to add invoice'], 500);
    }

    public function updateSnelstartInvoice($id, array $invoice)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/verkoopboekingen/' . $id;

        $response = Http::withToken($this->getSnelStartToken())
            ->put($url, $invoice);

        return $response->successful()
            ? response()->json(['message' => 'Invoice updated successfully'])
            : response()->json(['message' => 'Failed to update invoice'], 500);
    }

    public function deleteSnelstartInvoice($id)
    {
        $url = 'https://b2bapi.snelstart.nl/v2/verkoopboekingen/' . $id;

        $response = Http::withToken($this->getSnelStartToken())
            ->delete($url);

        return $response->successful()
            ? response()->json(['message' => 'Invoice deleted successfully'])
            : response()->json(['message' => 'Failed to delete invoice'], 500);
    }
}