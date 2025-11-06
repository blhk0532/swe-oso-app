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
            'is_active' => 'boolean',
            'is_telefon' => 'boolean',
            'is_ratsit' => 'boolean',
        ]);

        // Normalize and validate phone numbers (require at least 8 digits)
        $phones = [];
        if (isset($validated['telefon']) && $validated['telefon'] !== null) {
            $incoming = is_array($validated['telefon']) ? $validated['telefon'] : [];
            $phones = array_values(array_unique(array_filter(array_map(function ($n) {
                if (! is_string($n)) {
                    return '';
                }
                $n = trim($n);
                if ($n === '' || $n === ',') {
                    return '';
                }
                $digits = preg_replace('/[^0-9]/', '', $n);

                return strlen($digits) >= 8 ? $n : '';
            }, $incoming))));
        }

        $validated['telefon'] = $phones;
        $validated['is_telefon'] = count($phones) > 0;

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
