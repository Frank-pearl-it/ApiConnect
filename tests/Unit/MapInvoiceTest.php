<?php

namespace Tests\Feature;

use App\Services\AutotaskService;
use App\Services\MapService;
use App\Services\SnelstartService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MapInvoiceTest extends TestCase
{
    /** @test */
    public function it_maps_an_autotask_invoice_to_snelstart_format()
    {
        // Fake Autotask API response
        Http::fake([
            'webservices19.autotask.net/*' => Http::response([
                "items" => [
                    [
                        "id" => 155,
                        "batchID" => 79,
                        "comments" => null,
                        "companyID" => 369,
                        "createDateTime" => "2025-09-05T09:47:15.227Z",
                        "creatorResourceID" => 29682885,
                        "dueDate" => "2025-10-05T00:00:00.000Z",
                        "fromDate" => "2025-09-03T00:00:00.000Z",
                        "invoiceDateTime" => "2025-09-05T00:00:00.000Z",
                        "invoiceEditorTemplateID" => 102,
                        "invoiceNumber" => "1085",
                        "invoiceTotal" => 52.7775,
                        "isVoided" => false,
                        "orderNumber" => "",
                        "paidDate" => null,
                        "paymentTerm" => 1,  //NEED TO GET PAYMENT TERMS FROM MITCHELL TO MATCH AMOUNT OF DAYS
                        "taxGroup" => null,
                        "taxRegionName" => "VAT",
                        "toDate" => "2025-09-05T00=>00=>00.000Z",
                        "totalTaxValue" => 11.0833,
                        "voidedByResourceID" => null,
                        "voidedDate" => null,
                        "webServiceDate" => null,
                        "invoiceStatus" => 1,
                        "invoiceTaxMethodExternalCode" => null

                    ]
                ],
                "pageDetails" => [
                    "count" => 1,
                    "requestCount" => 500,
                    "prevPageUrl" => null,
                    "nextPageUrl" => null
                ]
            ], 200),
        ]);


        $autotaskService = new AutotaskService();
        $invoice = $autotaskService->getAutoTaskInvoice(155);

        // Fake a Snelstart Relatie UUID
        $snelstartRelatieId = '11111111-1111-1111-1111-111111111111';

        $mapped = MapService::mapInvoiceToVerkoopboeking($invoice, $snelstartRelatieId);

        $this->assertEquals('1085', $mapped['factuurnummer']);
        $this->assertEquals('2025-09-05T00:00:00.000Z', $mapped['factuurdatum']);
        $this->assertEquals($snelstartRelatieId, $mapped['klant']['id']);
        $this->assertEquals(52.7775, $mapped['factuurbedrag']);
        $this->assertEquals(1, $mapped['betalingstermijn']);
        $this->assertEquals('', $mapped['omschrijving']);
        $this->assertEquals(41.69, round($mapped['boekingsregels'][0]['bedrag'], 2));
        $this->assertEquals('Hoog', $mapped['boekingsregels'][0]['btwSoort']);
        $this->assertEquals(11.08, round($mapped['btw'][0]['btwBedrag'], 2));
    }

    /** @test */
    public function it_can_add_update_and_delete_invoice_in_snelstart()
    {
        Http::fake([
            'b2bapi.snelstart.nl/v2/verkoopboekingen' => Http::response([], 200),
            'b2bapi.snelstart.nl/v2/verkoopboekingen/*' => Http::response([], 200),
        ]);

        $snelstartService = new SnelstartService();

        $invoicePayload = [
            'factuurnummer' => '1085',
            'factuurdatum' => '2025-09-05',
            'klant' => ['id' => '11111111-1111-1111-1111-111111111111'],
            'factuurbedrag' => 52.78,
            'betalingstermijn' => 30,
            'boekingsregels' => [
                [
                    'omschrijving' => 'Autotask invoice line',
                    'bedrag' => 41.70,
                    'btwSoort' => 'Hoog',
                ]
            ],
            'btw' => [
                [
                    'btwSoort' => 'Hoog',
                    'btwBedrag' => 11.08,
                ]
            ],
        ];

        $addResponse = $snelstartService->addSnelstartInvoice($invoicePayload);
        $this->assertEquals(200, $addResponse->status());

        $updateResponse = $snelstartService->updateSnelstartInvoice('11111111-1111-1111-1111-111111111111', $invoicePayload);
        $this->assertEquals(200, $updateResponse->status());

        $deleteResponse = $snelstartService->deleteSnelstartInvoice('11111111-1111-1111-1111-111111111111');
        $this->assertEquals(200, $deleteResponse->status());
    }
}
