<?php

namespace App\Http\Controllers;

use App\Models\Drug;
use App\Models\User;
use App\Services\RxNormService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserDrugController extends Controller
{
    protected $rxNormService;

    public function __construct(RxNormService $rxNormService)
    {
        $this->rxNormService = $rxNormService;
    }

    public function index()
    {
        $user = Auth::user();
        $drugs = $user->drugs()->get();

        return response()->json($drugs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'rxcui' => 'required|string',
        ]);

        $rxcui = $request->input('rxcui');

        // Validate Rxcui with RxNorm API
        if (!$this->rxNormService->validateRxcui($rxcui)) {
            return response()->json(['message' => 'Invalid Rxcui'], 400);
        }

        // Check if drug already exists in our database
        $drug = Drug::where('rxcui', $rxcui)->first();

        if (!$drug) {
            // Get drug details from RxNorm
            $details = $this->rxNormService->getDrugDetails($rxcui);
            $nameResponse = $this->rxNormService->getDrugName($rxcui);

            $drug = Drug::create([
                'rxcui' => $rxcui,
                'name' => $nameResponse['name'] ?? 'Unknown Drug',
                'base_names' => $details['base_names'],
                'dosage_forms' => $details['dosage_forms'],
            ]);
        }

        // Attach drug to user if not already attached
        $user = Auth::user();
        if (!$user->drugs()->where('drug_id', $drug->id)->exists()) {
            $user->drugs()->attach($drug->id);
        }

        return response()->json([
            'message' => 'Drug added to your medication list',
            'drug' => $drug,
        ], 201);
    }

    public function destroy($rxcui)
    {
        $drug = Drug::where('rxcui', $rxcui)->firstOrFail();
        $user = Auth::user();

        if (!$user->drugs()->where('drug_id', $drug->id)->exists()) {
            return response()->json(['message' => 'Drug not found in your medication list'], 404);
        }

        $user->drugs()->detach($drug->id);

        return response()->json(['message' => 'Drug removed from your medication list']);
    }
}