<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;

class AutoTaskCompanyController extends Controller
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
    // TO BE EDITED SO THAT IT GETS THE OPEN TICKETS AS WELL
    // https://{{webservices[n]}}.autotask.net/atservicesrest/v1.0/tickets/query?search={"filter":[{"op":"noteq","field":"Status","value":5},{"op":"eq","field":"CompanyID","value":175}]}
    public function getAutoTaskCompanyById($id)
    {
        try {
            $company = $this->autotaskService->getAutoTaskCompany($id);

            if (!$company) {
                return response()->json(['message' => 'Autotask company not found'], 404);
            }

            return response()->json([
                'message' => 'Autotask company retrieved successfully',
                'data' => $company,
            ], 200);
        } catch (\Throwable $e) {
            \Log::error('Error fetching Autotask company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while fetching company'], 500);
        }
    }

    public function addSnelstartCompany($id)
    {
        try {
            $company = $this->autotaskService->getAutoTaskCompany($id);
            log::info('company', $company);

            if (!$company) {
                return response()->json(['message' => 'Autotask company not found'], 404);
            }
            $equalCompany = $this->snelstartService->getEqualCompany($company['adress1'] ?? '');
            log::info($equalCompany);
            if ($equalCompany && !empty($equalCompany['items'])) {
                return response()->json(['message' => 'Company with this BTW number already exists in SnelStart'], 409);
            }

            $mappedData = $this->mapService->mapCompanyToRelatie($company);

            // Properly structured log entry (human-readable + JSON formatted)
            Log::info('Mapped company data for SnelStart:', [
                'autotask_company_id' => $id,
                'mapped_data' => $mappedData
            ]);

            // return $this->snelstartService->addSnelstartCompany($mappedData);

            return response()->json(['message' => 'Company added succesfully'], 201);
        } catch (\Throwable $e) {
            Log::error('Error adding SnelStart company', [
                'autotask_company_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Internal error while adding company'], 500);
        }
    }

    public function updateSnelstartCompany($id)
    {
        try {
            $company = $this->autotaskService->getAutoTaskCompany($id);
            if (!$company) {
                return response()->json(['message' => 'Autotask company not found'], 404);
            }

            $equalCompany = $this->snelstartService->getEqualCompany($company['taxID'] ?? '');
            if (empty($equalCompany['items'][0]['id'])) {
                return response()->json(['message' => 'No existing company found in SnelStart'], 404);
            }

            $mappedData = $this->mapService->mapCompanyToRelatie($company);
            return $this->snelstartService->updateSnelstartCompany($equalCompany['items'][0]['id'], $mappedData);
        } catch (\Throwable $e) {
            Log::error('Error updating SnelStart company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while updating company'], 500);
        }
    }

    public function deleteSnelstartCompany($request)
    {
        try {
            $equalCompany = $this->snelstartService->getEqualCompany($request['btwNummer']);
            if ($equalCompany && !empty($equalCompany['items'])) {
                return $this->snelstartService->deleteSnelstartCompany($equalCompany['items'][0]['id']);
            }

            return response()->json(['message' => 'No company with this BTW number found in SnelStart'], 404);
        } catch (\Throwable $e) {
            Log::error('Error deleting SnelStart company: ' . $e->getMessage());
            return response()->json(['message' => 'Internal error while deleting company'], 500);
        }
    }
}
