<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;

 class AutoTaskInvoiceController extends Controller
{
    protected AutotaskService $autotaskService;
    protected MapService $mapService;
    protected SnelstartService $snelstartService;

    public function __construct(
        AutotaskService $autotaskService,
        MapService $mapService,
        SnelstartService $snelstartService
    ) {
        $this->autotaskService = $autotaskService;
        $this->mapService = $mapService;
        $this->snelstartService = $snelstartService;
    }

    public function addSnelstartInvoice($id)
    {
        try {
            $invoice = $this->autotaskService->getAutoTaskInvoice($id);
            if (!$invoice) {
                return response()->json(['message' => 'Autotask invoice not found'], 404);
            }

            $equalCompany = $this->snelstartService->getEqualCompany($invoice['companyId'] ?? '');
            $snelstartRelatieId = $equalCompany['items'][0]['id'] ?? null;

            if (!$snelstartRelatieId) {
                return response()->json(['message' => 'No matching SnelStart company found for this invoice'], 404);
            }

            $mappedData = $this->mapService->mapInvoiceToVerkoopboeking($invoice, $snelstartRelatieId);
            return $this->snelstartService->addSnelstartInvoice($mappedData);
        } catch (\Throwable $e) {
            Log::error('Error adding SnelStart invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while adding invoice'], 500);
        }
    }

    public function updateSnelstartInvoice($id)
    {
        try {
            $invoice = $this->autotaskService->getAutoTaskInvoice($id);
            if (!$invoice) {
                return response()->json(['message' => 'Autotask invoice not found'], 404);
            }

            $equalCompany = $this->snelstartService->getEqualCompany($invoice['companyId'] ?? '');
            $snelstartRelatieId = $equalCompany['items'][0]['id'] ?? null;

            if (!$snelstartRelatieId) {
                return response()->json(['message' => 'No matching SnelStart company found for this invoice'], 404);
            }

            $mappedData = $this->mapService->mapInvoiceToVerkoopboeking($invoice, $snelstartRelatieId);

            if (empty($mappedData['externId'])) {
                return response()->json(['message' => 'Missing external ID for update'], 400);
            }

            return $this->snelstartService->updateSnelstartInvoice($mappedData['externId'], $mappedData);
        } catch (\Throwable $e) {
            Log::error('Error updating SnelStart invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while updating invoice'], 500);
        }
    }

    public function deleteSnelstartInvoice($invoice)
    {
        try {
            $equalInvoice = $this->snelstartService->getEqualInvoice($invoice['id'] ?? '');
            if ($equalInvoice && !empty($equalInvoice['items'])) {
                return $this->snelstartService->deleteSnelstartInvoice($equalInvoice['items'][0]['id']);
            }

            return response()->json(['message' => 'No invoice with this ID found in SnelStart'], 404);
        } catch (\Throwable $e) {
            Log::error('Error deleting SnelStart invoice: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while deleting invoice'], 500);
        }
    }
}
