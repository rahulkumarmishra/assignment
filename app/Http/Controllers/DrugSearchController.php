<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\RxNormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DrugSearchController extends Controller
{
        protected $rxNormService;

    public function __construct(RxNormService $rxNormService)
    {
        $this->rxNormService = $rxNormService;
    }

    public function search(Request $request)
    {
        $request->validate([
            'drug_name' => 'required|string|min:3',
        ]);

        $drugName = $request->input('drug_name');
        $cacheKey = 'drug_search_' . md5($drugName);

        // Cache for 24 hours
        $results = Cache::remember($cacheKey, now()->addHours(24), function () use ($drugName) {
            return $this->rxNormService->searchDrugs($drugName);
        });

        return response()->json($results);
    }
}
