<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PersonerData;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PersonerDataController extends Controller
{
    public function bulkStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:100',
            'records.*.personnamn' => 'nullable|string',
            'records.*.alder' => 'nullable|string',
            'records.*.kon' => 'nullable|string',
            'records.*.gatuadress' => 'nullable|string',
            'records.*.postnummer' => 'nullable|string',
            'records.*.postort' => 'nullable|string',
            'records.*.hitta_data_id' => 'nullable|integer',
            'records.*.telefon' => 'nullable|array',
            'records.*.telefon.*' => 'nullable|string',
            'records.*.karta' => 'nullable|string',
            'records.*.link' => 'nullable|string',
            'records.*.bostadstyp' => 'nullable|string',
            'records.*.bostadspris' => 'nullable|string',
            'records.*.is_active' => 'nullable|boolean',
            'records.*.is_telefon' => 'nullable|boolean',
            'records.*.is_ratsit' => 'nullable|boolean',
            'records.*.is_hus' => 'nullable|boolean',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Map hitta data to personer_data hitta_ prefixed fields
                $personerData = [
                    'personnamn' => $recordData['personnamn'] ?? null,
                    'gatuadress' => $recordData['gatuadress'] ?? null,
                    'postnummer' => $recordData['postnummer'] ?? null,
                    'postort' => $recordData['postort'] ?? null,

                    // Hitta data fields
                    'hitta_data_id' => $recordData['hitta_data_id'] ?? null,
                    'hitta_personnamn' => $recordData['personnamn'] ?? null,
                    'hitta_gatuadress' => $recordData['gatuadress'] ?? null,
                    'hitta_postnummer' => $recordData['postnummer'] ?? null,
                    'hitta_postort' => $recordData['postort'] ?? null,
                    'hitta_alder' => $recordData['alder'] ?? null,
                    'hitta_kon' => $recordData['kon'] ?? null,
                    'hitta_telefon' => $recordData['telefon'] ?? null,
                    'hitta_karta' => $recordData['karta'] ?? null,
                    'hitta_link' => $recordData['link'] ?? null,
                    'hitta_bostadstyp' => $recordData['bostadstyp'] ?? null,
                    'hitta_bostadspris' => $recordData['bostadspris'] ?? null,
                    'hitta_is_active' => $recordData['is_active'] ?? true,
                    'hitta_is_telefon' => $recordData['is_telefon'] ?? false,
                    'hitta_is_hus' => $recordData['is_hus'] ?? true,
                    'hitta_updated_at' => now(), // Always update hitta_updated_at with current timestamp

                    'is_active' => true,
                ];

                // Use gatuadress + personnamn as the primary unique identifier (as requested)
                // This ensures we update existing records instead of creating duplicates
                if (!empty($recordData['gatuadress']) && !empty($recordData['personnamn'])) {
                    $existing = PersonerData::where('gatuadress', $recordData['gatuadress'])
                        ->where('personnamn', $recordData['personnamn'])
                        ->first();
                    if ($existing) {
                        // Update existing record
                        $existing->update($personerData);
                        $updated++;
                    } else {
                        // Create new record and set hitta_created_at
                        $personerData['hitta_created_at'] = now();
                        PersonerData::create($personerData);
                        $created++;
                    }
                } else {
                    // Fallback: try to find by hitta_link if gatuadress/personnamn are not available
                    if (!empty($recordData['link'])) {
                        $existing = PersonerData::where('hitta_link', $recordData['link'])->first();
                        if ($existing) {
                            // Update existing record
                            $existing->update($personerData);
                            $updated++;
                        } else {
                            // Create new record
                            $personerData['hitta_created_at'] = now();
                            PersonerData::create($personerData);
                            $created++;
                        }
                    } else {
                        // No reliable unique identifier, just create the record
                        $personerData['hitta_created_at'] = now();
                        PersonerData::create($personerData);
                        $created++;
                    }
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'personnamn' => $recordData['personnamn'] ?? 'unknown',
                    'gatuadress' => $recordData['gatuadress'] ?? 'unknown',
                    'link' => $recordData['link'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'data' => $recordData, // Include full data for debugging
                ];
                
                // Log the error for debugging
                \Log::error('PersonerData bulk store failed', [
                    'index' => $index,
                    'record' => $recordData,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json([
            'message' => 'Bulk personer_data operation completed',
            'summary' => [
                'total' => count($validated['records']),
                'created' => $created,
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }
}