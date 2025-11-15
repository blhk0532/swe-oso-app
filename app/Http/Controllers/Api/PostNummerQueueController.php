<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BulkUpdatePostNummerQueueRequest;
use App\Http\Requests\UpdatePostNummerQueueRequest;
use App\Http\Resources\PostNummerQueueResource;
use App\Models\PostNummerQueue;
use Exception;
use Illuminate\Http\JsonResponse;

class PostNummerQueueController extends Controller
{
    /**
     * Bulk update postnummer queue records.
     */
    public function bulkUpdate(BulkUpdatePostNummerQueueRequest $request): JsonResponse
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

                $record = PostNummerQueue::where('post_nummer', $normalizedPostNummer)->first();

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
     * Update postnummer queue record by post_nummer.
     */
    public function updateByPostNummer(UpdatePostNummerQueueRequest $request, string $postNummer): JsonResponse
    {
        $digitsOnly = preg_replace('/[^0-9]/', '', $postNummer);

        if (strlen($digitsOnly) >= 5) {
            $normalizedPostNummer = substr($digitsOnly, 0, 3) . ' ' . substr($digitsOnly, 3, 2);
        } else {
            $normalizedPostNummer = $digitsOnly;
        }

        $validated = $request->validated();

        $record = PostNummerQueue::where('post_nummer', $normalizedPostNummer)->firstOrFail();

        $record->update($validated);

        return response()->json([
            'message' => 'PostNummerQueue record updated successfully',
            'data' => new PostNummerQueueResource($record),
        ]);
    }
}
