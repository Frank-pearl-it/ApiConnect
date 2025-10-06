<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;

abstract class AutoTaskCompanyController extends Controller
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
        try {
            $company = $this->autotaskService->getAutoTaskCompany($id);
            if (!$company) {
                return response()->json(['message' => 'Autotask company not found'], 404);
            }

            $equalCompany = $this->snelstartService->getEqualCompany($company['taxID'] ?? '');
            if ($equalCompany && !empty($equalCompany['items'])) {
                return response()->json(['message' => 'Company with this BTW number already exists in SnelStart'], 409);
            }

            $mappedData = $this->mapService->mapCompanyToRelatie($company);
            return $this->snelstartService->addSnelstartCompany($mappedData);
        } catch (\Throwable $e) {
            Log::error('Error adding SnelStart company: ' . $e->getMessage());
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
