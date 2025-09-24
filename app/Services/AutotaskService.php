<?php

namespace App\Services;
use Illuminate\Support\Facades\Http;
class AutotaskService
{
    protected function autotaskRequest()
    {
        return Http::withHeaders([
            'ApiIntegrationCode' => env('AUTOTASK_API_CODE'),
            'UserName' => env('AUTOTASK_USERNAME'),
            'Secret' => env('AUTOTASK_SECRET'),
            'Content-Type' => 'application/json',
        ]);
    }

    public function getAutoTaskCompany($id)
    {
        $url = 'https://webservices19.autotask.net/atservicesrest/v1.0/Companies/' . $id;

        $response = $this->autotaskRequest()->get($url);

        if ($response->successful()) {
            $json = $response->json();
            return $json['item'] ?? null; // unwrap item
        }

        return null;
    }

    public function getAutoTaskInvoice($id)
    {
        $url = 'https://webservices19.autotask.net/atservicesrest/v1.0/Invoices/' . $id;

        $response = $this->autotaskRequest()->get($url);

        if ($response->successful()) {
            $json = $response->json();
            return $json['item'] ?? null; // unwrap just like with companies
        }

        return null;
    }

}