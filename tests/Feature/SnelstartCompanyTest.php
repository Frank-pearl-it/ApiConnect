<?php

namespace Tests\Feature;

use Tests\TestCase;

class SnelstartCompanyTest extends TestCase
{
    /**
     * Test the SnelStart company add route.
     */
    public function test_add_snelstart_company_route_returns_successful_response(): void
    {
        // You can change 123 to any valid test ID
        $response = $this->post('api/snelstart/companies/288');

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'mapped_data'
                 ]);
    }
}
