<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class RxNormService
{
    protected $client;
    protected $baseUrl = 'https://rxnav.nlm.nih.gov/REST/';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 10,
        ]);
    }

    public function searchDrugs($drugName, $tty = 'SBD', $limit = 5)
    {
        try {
            $response = $this->client->get("drug.json", [
                'query' => [
                    'name' => $drugName,
                    'tty' => $tty,
                ]
            ]);
            
            $data = json_decode($response->getBody()->getContents(), true);
            
            // Log raw response for debugging
            Log::info('RxNorm searchDrugs response', ['response' => $data]);

            $results = [];

            if (!empty($data['drugGroup']['conceptGroup'])) {
                foreach ($data['drugGroup']['conceptGroup'] as $group) {
                    if (!empty($group['conceptProperties'])) {
                        foreach (array_slice($group['conceptProperties'], 0, $limit) as $drug) {
                            $details = $this->getDrugDetails($drug['rxcui']);
                            $results[] = [
                                'rxcui' => $drug['rxcui'],
                                'name' => $drug['name'],
                                'base_names' => $details['base_names'] ?? [],
                                'dosage_forms' => $details['dosage_forms'] ?? [],
                            ];
                        }
                    }
                }
            }

            return $results;

        } catch (GuzzleException $e) {
            Log::error('RxNorm searchDrugs API error: ' . $e->getMessage());
            return [];
        }
    }

    public function getDrugDetails($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/{$rxcui}/historystatus.json");
            $data = json_decode($response->getBody()->getContents(), true);

            $baseNames = [];
            $dosageForms = [];

            if (!empty($data['rxcuiStatusHistory']['ingredientAndStrength'])) {
                foreach ($data['rxcuiStatusHistory']['ingredientAndStrength'] as $ingredient) {
                    if (!empty($ingredient['baseName'])) {
                        $baseNames[] = $ingredient['baseName'];
                    }
                }
            }

            if (!empty($data['rxcuiStatusHistory']['doseFormGroupConcept'])) {
                foreach ($data['rxcuiStatusHistory']['doseFormGroupConcept'] as $doseForm) {
                    if (!empty($doseForm['doseFormGroupName'])) {
                        $dosageForms[] = $doseForm['doseFormGroupName'];
                    }
                }
            }

            return [
                'base_names' => array_unique($baseNames),
                'dosage_forms' => array_unique($dosageForms),
            ];
        } catch (GuzzleException $e) {
            Log::error('RxNorm getDrugDetails API error: ' . $e->getMessage());
            return [
                'base_names' => [],
                'dosage_forms' => [],
            ];
        }
    }

    public function validateRxcui($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/{$rxcui}/properties.json");
            $data = json_decode($response->getBody()->getContents(), true);

            return isset($data['properties']);
        } catch (GuzzleException $e) {
            Log::error('RxNorm validateRxcui API error: ' . $e->getMessage());
            return false;
        }
    }

    public function getDrugName($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/{$rxcui}/properties.json");
            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'name' => $data['properties']['name'] ?? 'Unknown Drug',
            ];
        } catch (GuzzleException $e) {
            Log::error('RxNorm getDrugName API error: ' . $e->getMessage());
            return [
                'name' => 'Unknown Drug',
            ];
        }
    }
}
