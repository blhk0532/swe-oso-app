<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdateHittaQueueRequest;
use App\Http\Requests\UpdateHittaQueueRequest;
use App\Http\Resources\HittaQueueResource;
use App\Models\HittaQueue;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HittaQueueController extends Controller
{
    /**
     * Get the first hitta queue record where personer_queued = 1 and personer_scraped = 0
     */
    public function runPersoner(Request $request): JsonResponse
    {
        $record = HittaQueue::query()
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
     * Bulk update hitta queue records.
     */
    public function bulkUpdate(BulkUpdateHittaQueueRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $updated = 0;
        $failed = 0;
        $errors = [];

        foreach ($validated['records'] as $index => $recordData) {
            try {
                $digitsOnly = preg_replace('/[^0-9]/', '', $recordData['post_nummer']);

                if (strlen($digitsOnly) >= 5) {
                    $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
                } else {
                    $normalizedPostNummer = $digitsOnly;
                }

                $record = HittaQueue::where('post_nummer', $normalizedPostNummer)->first();

                if ($record) {
                    $updateData = $recordData;
                    unset($updateData['post_nummer']);

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
     * Update hitta queue record by post_nummer.
     */
    public function updateByPostNummer(UpdateHittaQueueRequest $request, string $postNummer): JsonResponse
    {
        $digitsOnly = preg_replace('/[^0-9]/', '', $postNummer);

        if (strlen($digitsOnly) >= 5) {
            $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
        } else {
            $normalizedPostNummer = $digitsOnly;
        }

        $validated = $request->validated();

        $record = HittaQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'HittaQueue record updated successfully',
            'data' => new HittaQueueResource($record),
        ]);
    }
}
