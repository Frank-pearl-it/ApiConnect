<?php
// tests/Feature/MapServiceTest.php

namespace Tests\Feature;

use App\Services\AutotaskService;
use App\Services\SnelstartService;
use App\Services\MapService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MapServiceTest extends TestCase
{
    /** @test */
    public function it_fetches_a_company_from_autotask_and_maps_it_to_snelstart_format()
    {
        // Fake the Autotask API response
        Http::fake([
            'webservices19.autotask.net/*' => Http::response([
                'item' => [
                    'companyName'   => 'TestCo BV',
                    'companyNumber' => '12345',
                    'taxID'         => 'NL123456789B01',
                    'isActive'      => true,
                    'phone'         => '013-1234567',
                    'alternatePhone1' => '06-12345678',
                    'email'         => 'info@testco.nl',
                    'webAddress'    => 'https://testco.nl',
                    'address1'      => 'Main Street',
                    'address2'      => '12A',
                    'postalCode'    => '5038ED',
                    'city'          => 'Tilburg',
                    'state'         => 'NB',
                    'countryID'     => 'NL',
                ]
            ], 200),
        ]);

        $autotaskService = new AutotaskService();
        $company = $autotaskService->getAutoTaskCompany(0);

        $mapped = MapService::mapCompanyToRelatie($company);

        $this->assertEquals('TestCo BV', $mapped['naam']);
        $this->assertEquals('12345', $mapped['relatiecode']);
        $this->assertEquals('NL123456789B01', $mapped['btwNummer']);
        $this->assertEquals(false, $mapped['nonactief']); // isActive = true → nonactief = false
        $this->assertEquals('Main Street', $mapped['vestigingsAdres']['straat']);
        $this->assertEquals('Tilburg', $mapped['vestigingsAdres']['plaats']);
    } 

    /** @test */
    public function it_adds_updates_and_deletes_a_company_in_snelstart()
    {
        $snelstartService = new SnelstartService();

        $fakeCompany = [
            'naam' => 'TestCo BV',
            'relatiecode' => '12345',
            'btwNummer' => 'NL123456789B01',
            'nonactief' => false,
            'vestigingsAdres' => [
                'straat' => 'Main Street',
                'huisnummer' => '12A',
                'postcode' => '5038ED',
                'plaats' => 'Tilburg',
                'provincie' => 'NB',
                'land' => 'NL',
            ],
        ];

        // Fake add
        Http::fake([
            'b2bapi.snelstart.nl/v2/relaties' => Http::response(['id' => 'abc-123'], 200),
        ]);
        $addResponse = $snelstartService->addSnelstartCompany($fakeCompany);
        $this->assertEquals(200, $addResponse->status());

        // Fake update
        Http::fake([
            'b2bapi.snelstart.nl/v2/relaties/abc-123' => Http::response(['message' => 'Company updated successfully'], 200),
        ]);
        $updateResponse = $snelstartService->updateSnelstartCompany('abc-123', $fakeCompany);
        $this->assertEquals(200, $updateResponse->status());

        // Fake delete
        Http::fake([
            'b2bapi.snelstart.nl/v2/relaties/abc-123' => Http::response(['message' => 'Company deleted successfully'], 200),
        ]);
        $deleteResponse = $snelstartService->deleteSnelstartCompany('abc-123');
        $this->assertEquals(200, $deleteResponse->status());
    }
}
