<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Http;
use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;
class ApiController extends Controller
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

    

    

}
