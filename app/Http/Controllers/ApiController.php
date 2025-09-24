<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;
abstract class ApiController extends Controller
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

    public function addSnelstartCompany($id)
    {
        $company = $this->autotaskService->getAutoTaskCompany($id);
        $equalCompany = $this->snelstartService->getEqualCompany($company['taxID'] ?? '');
        if ($equalCompany && !empty($equalCompany['items'])) {
            return response()->json(['message' => 'Company with this BTW number already exists in SnelStart'], 409);
        }
        if ($company) {
            $mappedData = $this->mapService->mapCompanyToRelatie($company);
            return $this->snelstartService->addSnelstartCompany($mappedData);
        }
    }

    public function updateSnelstartCompany($id)
    {
        $company = $this->autotaskService->getAutoTaskCompany($id);
        $equalCompany = $this->snelstartService->getEqualCompany($company['taxID'] ?? '');
        if ($company) {
            $mappedData = $this->mapService->mapCompanyToRelatie($company);
            return $this->snelstartService->updateSnelstartCompany($equalCompany['items'][0]['id'], $mappedData);
        }
    }

    public function deleteSnelstartCompany($request)
    {
        $equalCompany = $this->snelstartService->getEqualCompany($request['btwNummer']);
        if ($equalCompany && !empty($equalCompany['items'])) {
            return $this->snelstartService->deleteSnelstartCompany($equalCompany['items'][0]['id']);
        }
        return response()->json(['message' => 'No company with this BTW number found in SnelStart'], 404);
    }

    public function addSnelstartInvoice($id)
    {
        $invoice = $this->autotaskService->getAutoTaskInvoice($id);
        if ($invoice) {
            $mappedData = $this->mapService->mapInvoiceToVerkoopboeking($invoice, $this->snelstartService->getEqualCompany($invoice['companyId'] ?? '')['items'][0] ?? []);
            return $this->snelstartService->addSnelstartInvoice($mappedData);
        }
    }

    public function updateSnelstartInvoice($id)
    {
        $invoice = $this->autotaskService->getAutoTaskInvoice($id);
        if ($invoice) {
            $mappedData = $this->mapService->mapInvoiceToVerkoopboeking($invoice, $this->snelstartService->getEqualCompany($invoice['companyId'] ?? '')['items'][0] ?? []);
            return $this->snelstartService->updateSnelstartInvoice($mappedData['id'], $mappedData);
        }
    }

    public function deleteSnelstartInvoice($invoice)
    {
        $equalInvoice = $this->snelstartService->getEqualInvoice($invoice['id'] ?? '');
        if ($equalInvoice && !empty($equalInvoice['items'])) {
            return $this->snelstartService->deleteSnelstartInvoice($equalInvoice['items'][0]['id']);
        }
        return response()->json(['message' => 'No invoice with this ID found in SnelStart'], 404);
    }

}
