<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DrugSearchTest extends TestCase
{
    public function test_drug_search_requires_drug_name()
    {
        $response = $this->json('GET', '/api/drugs/search');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['drug_name']);
    }

    public function test_drug_search_returns_results()
    {
        // Mock the RxNorm service or use actual API in tests
        $response = $this->json('GET', '/api/drugs/search', [
            'drug_name' => 'aspirin'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([[
                'rxcui',
                'name',
                'base_names',
                'dosage_forms'
            ]]);
    }

    public function test_drug_search_rate_limiting()
    {
        for ($i = 0; $i < 15; $i++) {
            $response = $this->json('GET', '/api/drugs/search', [
                'drug_name' => 'test' . $i
            ]);
            
            if ($i >= 10) {
                $response->assertStatus(429);
            }
        }
    }
}