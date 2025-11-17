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
            'records.*.ratsit_data_id' => 'nullable|integer',
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
            
            // Ratsit data fields
            'records.*.ratsit_gatuadress' => 'nullable|string',
            'records.*.ratsit_postnummer' => 'nullable|string',
            'records.*.ratsit_postort' => 'nullable|string',
            'records.*.ratsit_forsamling' => 'nullable|string',
            'records.*.ratsit_kommun' => 'nullable|string',
            'records.*.ratsit_lan' => 'nullable|string',
            'records.*.ratsit_adressandring' => 'nullable|string',
            'records.*.ratsit_kommun_ratsit' => 'nullable|string',
            'records.*.ratsit_stjarntacken' => 'nullable|string',
            'records.*.ratsit_fodelsedag' => 'nullable|string',
            'records.*.ratsit_personnummer' => 'nullable|string',
            'records.*.ratsit_alder' => 'nullable|string',
            'records.*.ratsit_kon' => 'nullable|string',
            'records.*.ratsit_civilstand' => 'nullable|string',
            'records.*.ratsit_fornamn' => 'nullable|string',
            'records.*.ratsit_efternamn' => 'nullable|string',
            'records.*.ratsit_personnamn' => 'nullable|string',
            'records.*.ratsit_agandeform' => 'nullable|string',
            'records.*.ratsit_bostadstyp' => 'nullable|string',
            'records.*.ratsit_boarea' => 'nullable|string',
            'records.*.ratsit_byggar' => 'nullable|string',
            'records.*.ratsit_fastighet' => 'nullable|string',
            'records.*.ratsit_telfonnummer' => 'nullable|array',
            'records.*.ratsit_telfonnummer.*' => 'nullable|string',
            'records.*.ratsit_epost_adress' => 'nullable|array',
            'records.*.ratsit_epost_adress.*' => 'nullable|string',
            'records.*.ratsit_personer' => 'nullable|array',
            'records.*.ratsit_foretag' => 'nullable|array',
            'records.*.ratsit_grannar' => 'nullable|array',
            'records.*.ratsit_fordon' => 'nullable|array',
            'records.*.ratsit_hundar' => 'nullable|array',
            'records.*.ratsit_bolagsengagemang' => 'nullable|array',
            'records.*.ratsit_longitude' => 'nullable|string',
            'records.*.ratsit_latitud' => 'nullable|string',
            'records.*.ratsit_google_maps' => 'nullable|string',
            'records.*.ratsit_google_streetview' => 'nullable|string',
            'records.*.ratsit_ratsit_se' => 'nullable|string',
            'records.*.ratsit_is_active' => 'nullable|boolean',
            'records.*.ratsit_updated_at' => 'nullable|string',
            
            // Merinfo data fields
            'records.*.merinfo_data_id' => 'nullable|integer',
            'records.*.merinfo_personnamn' => 'nullable|string',
            'records.*.merinfo_alder' => 'nullable|string',
            'records.*.merinfo_kon' => 'nullable|string',
            'records.*.merinfo_gatuadress' => 'nullable|string',
            'records.*.merinfo_postnummer' => 'nullable|string',
            'records.*.merinfo_postort' => 'nullable|string',
            'records.*.merinfo_telefon' => 'nullable|array',
            'records.*.merinfo_telefon.*' => 'nullable|string',
            'records.*.merinfo_karta' => 'nullable|string',
            'records.*.merinfo_link' => 'nullable|string',
            'records.*.merinfo_bostadstyp' => 'nullable|string',
            'records.*.merinfo_bostadspris' => 'nullable|string',
            'records.*.merinfo_is_active' => 'nullable|boolean',
            'records.*.merinfo_is_telefon' => 'nullable|boolean',
            'records.*.merinfo_is_hus' => 'nullable|boolean',
            'records.*.merinfo_created_at' => 'nullable|string',
            'records.*.merinfo_updated_at' => 'nullable|string',
        ]);

        $created = 0;
        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Base identifying fields
                $personerData = [
                    'personnamn' => $recordData['personnamn'] ?? null,
                    'gatuadress' => $recordData['gatuadress'] ?? null,
                    'postnummer' => $recordData['postnummer'] ?? null,
                    'postort' => $recordData['postort'] ?? null,
                    'is_active' => true,
                ];

                // Hitta data fields - only add if hitta_data_id is present
                if (isset($recordData['hitta_data_id'])) {
                    $personerData['hitta_data_id'] = $recordData['hitta_data_id'];
                    $personerData['hitta_personnamn'] = $recordData['personnamn'] ?? null;
                    $personerData['hitta_gatuadress'] = $recordData['gatuadress'] ?? null;
                    $personerData['hitta_postnummer'] = $recordData['postnummer'] ?? null;
                    $personerData['hitta_postort'] = $recordData['postort'] ?? null;
                    $personerData['hitta_alder'] = $recordData['alder'] ?? null;
                    $personerData['hitta_kon'] = $recordData['kon'] ?? null;
                    $personerData['hitta_telefon'] = $recordData['telefon'] ?? null;
                    $personerData['hitta_karta'] = $recordData['karta'] ?? null;
                    $personerData['hitta_link'] = $recordData['link'] ?? null;
                    $personerData['hitta_bostadstyp'] = $recordData['bostadstyp'] ?? null;
                    $personerData['hitta_bostadspris'] = $recordData['bostadspris'] ?? null;
                    $personerData['hitta_is_active'] = $recordData['is_active'] ?? true;
                    $personerData['hitta_is_telefon'] = $recordData['is_telefon'] ?? false;
                    $personerData['hitta_is_hus'] = $recordData['is_hus'] ?? true;
                    $personerData['hitta_updated_at'] = now();
                }

                // Ratsit data fields - only add if ratsit_data_id is present
                if (isset($recordData['ratsit_data_id'])) {
                    $personerData['ratsit_data_id'] = $recordData['ratsit_data_id'];
                    $personerData['ratsit_gatuadress'] = $recordData['ratsit_gatuadress'] ?? null;
                    $personerData['ratsit_postnummer'] = $recordData['ratsit_postnummer'] ?? null;
                    $personerData['ratsit_postort'] = $recordData['ratsit_postort'] ?? null;
                    $personerData['ratsit_forsamling'] = $recordData['ratsit_forsamling'] ?? null;
                    $personerData['ratsit_kommun'] = $recordData['ratsit_kommun'] ?? null;
                    $personerData['ratsit_lan'] = $recordData['ratsit_lan'] ?? null;
                    $personerData['ratsit_adressandring'] = $recordData['ratsit_adressandring'] ?? null;
                    $personerData['ratsit_kommun_ratsit'] = $recordData['ratsit_kommun_ratsit'] ?? null;
                    $personerData['ratsit_stjarntacken'] = $recordData['ratsit_stjarntacken'] ?? null;
                    $personerData['ratsit_fodelsedag'] = $recordData['ratsit_fodelsedag'] ?? null;
                    $personerData['ratsit_personnummer'] = $recordData['ratsit_personnummer'] ?? null;
                    $personerData['ratsit_alder'] = $recordData['ratsit_alder'] ?? null;
                    $personerData['ratsit_kon'] = $recordData['ratsit_kon'] ?? null;
                    $personerData['ratsit_civilstand'] = $recordData['ratsit_civilstand'] ?? null;
                    $personerData['ratsit_fornamn'] = $recordData['ratsit_fornamn'] ?? null;
                    $personerData['ratsit_efternamn'] = $recordData['ratsit_efternamn'] ?? null;
                    $personerData['ratsit_personnamn'] = $recordData['ratsit_personnamn'] ?? null;
                    $personerData['ratsit_agandeform'] = $recordData['ratsit_agandeform'] ?? null;
                    $personerData['ratsit_bostadstyp'] = $recordData['ratsit_bostadstyp'] ?? null;
                    $personerData['ratsit_boarea'] = $recordData['ratsit_boarea'] ?? null;
                    $personerData['ratsit_byggar'] = $recordData['ratsit_byggar'] ?? null;
                    $personerData['ratsit_fastighet'] = $recordData['ratsit_fastighet'] ?? null;
                    $personerData['ratsit_telfonnummer'] = $recordData['ratsit_telfonnummer'] ?? null;
                    $personerData['ratsit_epost_adress'] = $recordData['ratsit_epost_adress'] ?? null;
                    $personerData['ratsit_personer'] = $recordData['ratsit_personer'] ?? null;
                    $personerData['ratsit_foretag'] = $recordData['ratsit_foretag'] ?? null;
                    $personerData['ratsit_grannar'] = $recordData['ratsit_grannar'] ?? null;
                    $personerData['ratsit_fordon'] = $recordData['ratsit_fordon'] ?? null;
                    $personerData['ratsit_hundar'] = $recordData['ratsit_hundar'] ?? null;
                    $personerData['ratsit_bolagsengagemang'] = $recordData['ratsit_bolagsengagemang'] ?? null;
                    $personerData['ratsit_longitude'] = $recordData['ratsit_longitude'] ?? null;
                    $personerData['ratsit_latitud'] = $recordData['ratsit_latitud'] ?? null;
                    $personerData['ratsit_google_maps'] = $recordData['ratsit_google_maps'] ?? null;
                    $personerData['ratsit_google_streetview'] = $recordData['ratsit_google_streetview'] ?? null;
                    $personerData['ratsit_ratsit_se'] = $recordData['ratsit_ratsit_se'] ?? null;
                    $personerData['ratsit_is_active'] = $recordData['ratsit_is_active'] ?? true;
                    $personerData['ratsit_updated_at'] = $recordData['ratsit_updated_at'] ?? now();
                }

                // Merinfo data fields - only add if merinfo_data_id is present
                if (isset($recordData['merinfo_data_id'])) {
                    $personerData['merinfo_data_id'] = $recordData['merinfo_data_id'];
                    $personerData['merinfo_personnamn'] = $recordData['merinfo_personnamn'] ?? null;
                    $personerData['merinfo_alder'] = $recordData['merinfo_alder'] ?? null;
                    $personerData['merinfo_kon'] = $recordData['merinfo_kon'] ?? null;
                    $personerData['merinfo_gatuadress'] = $recordData['merinfo_gatuadress'] ?? null;
                    $personerData['merinfo_postnummer'] = $recordData['merinfo_postnummer'] ?? null;
                    $personerData['merinfo_postort'] = $recordData['merinfo_postort'] ?? null;
                    $personerData['merinfo_telefon'] = $recordData['merinfo_telefon'] ?? null;
                    $personerData['merinfo_karta'] = $recordData['merinfo_karta'] ?? null;
                    $personerData['merinfo_link'] = $recordData['merinfo_link'] ?? null;
                    $personerData['merinfo_bostadstyp'] = $recordData['merinfo_bostadstyp'] ?? null;
                    $personerData['merinfo_bostadspris'] = $recordData['merinfo_bostadspris'] ?? null;
                    $personerData['merinfo_is_active'] = $recordData['merinfo_is_active'] ?? true;
                    $personerData['merinfo_is_telefon'] = $recordData['merinfo_is_telefon'] ?? false;
                    $personerData['merinfo_is_hus'] = $recordData['merinfo_is_hus'] ?? true;
                    $personerData['merinfo_created_at'] = $recordData['merinfo_created_at'] ?? null;
                    $personerData['merinfo_updated_at'] = $recordData['merinfo_updated_at'] ?? now();
                }

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