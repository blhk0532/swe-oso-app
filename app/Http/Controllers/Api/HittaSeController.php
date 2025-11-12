<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HittaSe;
use Exception;
use Illuminate\Http\Request;
use Log;

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
        if (isset($validated['link']) && $validated['link']) {
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

    public function batchStore(Request $request)
    {
        $validated = $request->validate([
            'records' => 'required|array',
            'records.*.personnamn' => 'nullable|string',
            'records.*.alder' => 'nullable|string',
            'records.*.kon' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.telefon' => 'nullable|array',
            'records.*.telefon.*' => 'nullable|string',
            'records.*.karta' => 'nullable|string',
            'records.*.link' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.bostadspris' => 'nullable|string',
            'records.*.is_active' => 'boolean',
            'records.*.is_telefon' => 'boolean',
            'records.*.is_ratsit' => 'boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;

        foreach ($validated['records'] as $recordData) {
            try {
                // Check if record already exists by link (unique identifier)
                if (isset($recordData['link']) && $recordData['link']) {
                    $existing = HittaSe::where('link', $recordData['link'])->first();
                    if ($existing) {
                        // Update existing record
                        $existing->update($recordData);
                        $updated++;

                        continue;
                    }
                }

                // Create new record
                HittaSe::create($recordData);
                $created++;
            } catch (Exception $e) {
                $failed++;
                Log::error('Batch store failed for record: ' . json_encode($recordData) . ' Error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Batch processing complete',
            'created' => $created,
            'updated' => $updated,
            'failed' => $failed,
            'total' => count($validated['records']),
        ], 200);
    }
}
