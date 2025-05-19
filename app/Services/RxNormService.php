<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

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
            $response = $this->client->get("drugs?name=$drugName&tty=$tty");
            $data = json_decode($response->getBody(), true);

            $results = [];
            if (isset($data['drugGroup']['conceptGroup'])) {
                foreach ($data['drugGroup']['conceptGroup'] as $group) {
                    if (isset($group['conceptProperties'])) {
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
            return [];
        }
    }

    public function getDrugDetails($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/$rxcui/historystatus.json");
            $data = json_decode($response->getBody(), true);

            $baseNames = [];
            $dosageForms = [];

            if (isset($data['rxcuiStatusHistory']['ingredientAndStrength'])) {
                foreach ($data['rxcuiStatusHistory']['ingredientAndStrength'] as $ingredient) {
                    if (isset($ingredient['baseName'])) {
                        $baseNames[] = $ingredient['baseName'];
                    }
                }
            }

            if (isset($data['rxcuiStatusHistory']['doseFormGroupConcept'])) {
                foreach ($data['rxcuiStatusHistory']['doseFormGroupConcept'] as $doseForm) {
                    if (isset($doseForm['doseFormGroupName'])) {
                        $dosageForms[] = $doseForm['doseFormGroupName'];
                    }
                }
            }

            return [
                'base_names' => array_unique($baseNames),
                'dosage_forms' => array_unique($dosageForms),
            ];
        } catch (GuzzleException $e) {
            return [
                'base_names' => [],
                'dosage_forms' => [],
            ];
        }
    }

    public function validateRxcui($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/$rxcui/properties.json");
            $data = json_decode($response->getBody(), true);

            return isset($data['properties']);
        } catch (GuzzleException $e) {
            return false;
        }
    }

    public function getDrugName($rxcui)
    {
        try {
            $response = $this->client->get("rxcui/$rxcui/properties.json");
            $data = json_decode($response->getBody(), true);

            return [
                'name' => $data['properties']['name'] ?? 'Unknown Drug',
            ];
        } catch (GuzzleException $e) {
            return [
                'name' => 'Unknown Drug',
            ];
        }
    }
}