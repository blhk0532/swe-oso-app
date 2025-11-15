<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HittaBolag;
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

        // Determine which table to save to based on "kon" field
        // If "kon" is missing or empty, save to hitta_bolag (company data)
        // If "kon" exists, save to hitta_se (person data)
        $isCompanyData = ! isset($validated['kon']) || empty(trim($validated['kon'] ?? ''));

        if ($isCompanyData) {
            return $this->storeToHittaBolag($validated);
        } else {
            return $this->storeToHittaSe($validated);
        }
    }

    /**
     * Store data to hitta_se table (person data with gender)
     */
    private function storeToHittaSe($validated)
    {
        // Check if record already exists by link (unique identifier)
        if (isset($validated['link']) && $validated['link']) {
            $existing = HittaSe::where('link', $validated['link'])->first();
            if ($existing) {
                // Update existing record
                $existing->update($validated);

                return response()->json([
                    'message' => 'HittaSe record updated successfully',
                    'data' => $existing,
                ], 200);
            }
        }

        // Create new record
        $record = HittaSe::create($validated);

        return response()->json([
            'message' => 'HittaSe record created successfully',
            'data' => $record,
        ], 201);
    }

    /**
     * Store data to hitta_bolag table (company data without gender)
     */
    private function storeToHittaBolag($validated)
    {
        // Import HittaBolag model
        $hittaBolagModel = app(HittaBolag::class);

        // Map fields for hitta_bolag (different column names)
        $bolagData = [
            'personnamn' => $validated['personnamn'] ?? null, // juridiskt_namn in hitta_bolag
            'juridiskt_namn' => $validated['personnamn'] ?? null, // Company name
            'registreringsdatum' => $validated['alder'] ?? null, // Registration date
            'org_nr' => null, // Will be filled separately if available
            'bolagsform' => null, // Will be filled separately if available
            'sni_branch' => [], // Will be filled separately if available
            'gatuadress' => $validated['gatuadress'] ?? null,
            'postnummer' => $validated['postnummer'] ?? null,
            'postort' => $validated['postort'] ?? null,
            'telefon' => $validated['telefon'] ?? null,
            'karta' => $validated['karta'] ?? null,
            'link' => $validated['link'] ?? null,
            'bostadstyp' => $validated['bostadstyp'] ?? null,
            'bostadspris' => $validated['bostadspris'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'is_telefon' => $validated['is_telefon'] ?? false,
            'is_ratsit' => $validated['is_ratsit'] ?? false,
        ];

        // Check if record already exists by link (unique identifier)
        if (isset($bolagData['link']) && $bolagData['link']) {
            $existing = HittaBolag::where('link', $bolagData['link'])->first();
            if ($existing) {
                // Update existing record
                $existing->update($bolagData);

                return response()->json([
                    'message' => 'HittaBolag record updated successfully',
                    'data' => $existing,
                ], 200);
            }
        }

        // Create new record
        $record = HittaBolag::create($bolagData);

        return response()->json([
            'message' => 'HittaBolag record created successfully',
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
        $seCreated = 0;
        $seUpdated = 0;
        $bolagCreated = 0;
        $bolagUpdated = 0;

        foreach ($validated['records'] as $recordData) {
            try {
                // Determine which table to save to based on "kon" field
                $isCompanyData = ! isset($recordData['kon']) || empty(trim($recordData['kon'] ?? ''));

                if ($isCompanyData) {
                    $result = $this->storeToHittaBolag($recordData);
                    if ($result->getStatusCode() === 200) {
                        $bolagUpdated++;
                    } elseif ($result->getStatusCode() === 201) {
                        $bolagCreated++;
                    }
                } else {
                    $result = $this->storeToHittaSeBatch($recordData);
                    if ($result['action'] === 'updated') {
                        $seUpdated++;
                    } elseif ($result['action'] === 'created') {
                        $seCreated++;
                    }
                }
            } catch (Exception $e) {
                $failed++;
                Log::error('Batch store failed for record: ' . json_encode($recordData) . ' Error: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Batch processing complete',
            'hitta_se' => [
                'created' => $seCreated,
                'updated' => $seUpdated,
            ],
            'hitta_bolag' => [
                'created' => $bolagCreated,
                'updated' => $bolagUpdated,
            ],
            'failed' => $failed,
            'total' => count($validated['records']),
        ], 200);
    }

    /**
     * Store data to hitta_se table for batch processing (returns action type)
     */
    private function storeToHittaSeBatch($recordData)
    {
        // Check if record already exists by link (unique identifier)
        if (isset($recordData['link']) && $recordData['link']) {
            $existing = HittaSe::where('link', $recordData['link'])->first();
            if ($existing) {
                // Update existing record
                $existing->update($recordData);

                return ['action' => 'updated', 'record' => $existing];
            }
        }

        // Create new record
        $record = HittaSe::create($recordData);

        return ['action' => 'created', 'record' => $record];
    }
}
