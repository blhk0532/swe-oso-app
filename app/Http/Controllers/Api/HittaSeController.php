<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HittaSe;
use Illuminate\Http\Request;

class HittaSeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'personnamn' => 'nullable|string',
            'alder' => 'nullable|string',
            'kon' => 'nullable|string',
            'gatuadress' => 'nullable|string',
            'postnummer' => 'nullable|string',
            'postort' => 'nullable|string',
            'telefon' => 'nullable|array',
            'telefon.*' => 'nullable|string',
            'karta' => 'nullable|string',
            'link' => 'nullable|string',
            'bostadstyp' => 'nullable|string',
            'bostadspris' => 'nullable|string',
            'is_active' => 'boolean',
            'is_telefon' => 'boolean',
            'is_ratsit' => 'boolean',
        ]);

        // Check if record already exists by link (unique identifier)
        if ($validated['link']) {
            $existing = HittaSe::where('link', $validated['link'])->first();
            if ($existing) {
                // Update existing record
                $existing->update($validated);

                return response()->json([
                    'message' => 'Record updated successfully',
                    'data' => $existing,
                ], 200);
            }
        }

        // Create new record
        $record = HittaSe::create($validated);

        return response()->json([
            'message' => 'Record created successfully',
            'data' => $record,
        ], 201);
    }
}
