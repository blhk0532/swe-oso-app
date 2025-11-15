<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MerinfoQueue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerinfoQueueController extends Controller
{
    /**
     * Get the first merinfo queue record where personer_queued = 1 and personer_scraped = 0
     */
    public function runPersoner(Request $request): JsonResponse
    {
        $record = MerinfoQueue::query()
            ->where('personer_queued', 1)
            ->where('personer_scraped', 0)
            ->orderBy('created_at', 'asc')
            ->first();

        if (! $record) {
            return response()->json([
                'message' => 'No records found with personer_queued = 1 and personer_scraped = 0',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'message' => 'Found record for personer processing',
            'data' => [
                'id' => $record->id,
                'post_nummer' => str_replace(' ', '', $record->post_nummer),
                'post_ort' => $record->post_ort,
                'post_lan' => $record->post_lan,
                'foretag_total' => $record->foretag_total,
                'personer_total' => $record->personer_total,
                'personer_house' => $record->personer_house,
                'foretag_phone' => $record->foretag_phone,
                'personer_phone' => $record->personer_phone,
                'foretag_saved' => $record->foretag_saved,
                'personer_saved' => $record->personer_saved,
                'foretag_queued' => $record->foretag_queued,
                'personer_queued' => $record->personer_queued,
                'foretag_scraped' => $record->foretag_scraped,
                'personer_scraped' => $record->personer_scraped,
                'is_active' => $record->is_active,
                'created_at' => $record->created_at,
                'updated_at' => $record->updated_at,
            ],
        ]);
    }

    /**
     * Bulk update merinfo queue records.
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'records' => 'required|array|min:1|max:50',
            'records.*.post_nummer' => 'required|string|max:10',
            'records.*.post_ort' => 'nullable|string',
            'records.*.post_lan' => 'nullable|string',
            'records.*.foretag_total' => 'sometimes|integer|min:0',
            'records.*.personer_total' => 'sometimes|integer|min:0',
            'records.*.personer_house' => 'sometimes|integer|min:0',
            'records.*.foretag_phone' => 'sometimes|integer|min:0',
            'records.*.personer_phone' => 'sometimes|integer|min:0',
            'records.*.foretag_saved' => 'sometimes|integer|min:0',
            'records.*.personer_saved' => 'sometimes|integer|min:0',
            'records.*.foretag_queued' => 'sometimes|integer|min:0',
            'records.*.personer_queued' => 'sometimes|integer|min:0',
            'records.*.foretag_scraped' => 'sometimes|boolean',
            'records.*.personer_scraped' => 'sometimes|boolean',
            'records.*.is_active' => 'sometimes|boolean',
        ]);

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                // Normalize the post_nummer to Swedish postal code format (XXX XX)
                $digitsOnly = preg_replace('/[^0-9]/', '', $recordData['post_nummer']);

                // Format as XXX XX if we have at least 5 digits
                if (strlen($digitsOnly) >= 5) {
                    $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
                } else {
                    $normalizedPostNummer = $digitsOnly;
                }

                $record = MerinfoQueue::where('post_nummer', $normalizedPostNummer)->first();

                if ($record) {
                    $updateData = $recordData;
                    unset($updateData['post_nummer']); // Don't update the key field

                    $record->update($updateData);
                    $updated++;
                } else {
                    $failed++;
                    $errors[] = [
                        'index' => $index,
                        'post_nummer' => $recordData['post_nummer'],
                        'error' => 'Record not found',
                    ];
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = [
                    'index' => $index,
                    'post_nummer' => $recordData['post_nummer'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Bulk update completed',
            'summary' => [
                'total' => count($validated['records']),
                'updated' => $updated,
                'failed' => $failed,
            ],
            'errors' => $errors,
        ]);
    }

    /**
     * Update merinfo queue record by post_nummer.
     */
    public function updateByPostNummer(Request $request, string $postNummer): JsonResponse
    {
        // Normalize the post_nummer to Swedish postal code format (XXX XX)
        $digitsOnly = preg_replace('/[^0-9]/', '', $postNummer);

        // Format as XXX XX if we have at least 5 digits
        if (strlen($digitsOnly) >= 5) {
            $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
        } else {
            $normalizedPostNummer = $digitsOnly;
        }

        $validated = $request->validate([
            'post_ort' => 'nullable|string',
            'post_lan' => 'nullable|string',
            'foretag_total' => 'sometimes|integer|min:0',
            'personer_total' => 'sometimes|integer|min:0',
            'personer_house' => 'sometimes|integer|min:0',
            'foretag_phone' => 'sometimes|integer|min:0',
            'personer_phone' => 'sometimes|integer|min:0',
            'foretag_saved' => 'sometimes|integer|min:0',
            'personer_saved' => 'sometimes|integer|min:0',
            'foretag_queued' => 'sometimes|integer|min:0',
            'personer_queued' => 'sometimes|integer|min:0',
            'foretag_scraped' => 'sometimes|boolean',
            'personer_scraped' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
        ]);

        $record = MerinfoQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'MerinfoQueue record updated successfully',
            'data' => $record,
        ]);
    }
}
