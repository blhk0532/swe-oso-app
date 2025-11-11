<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostNummerApiRequest;
use App\Http\Requests\UpdatePostNummerApiRequest;
use App\Http\Resources\PostNummerApiResource;
use App\Models\PostNummerApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostNummerApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PostNummerApi::query();

        // Filter by post_nummer
        if ($request->has('post_nummer')) {
            $query->where('post_nummer', 'like', "%{$request->post_nummer}%");
        }

        // Filter by post_ort
        if ($request->has('post_ort')) {
            $query->where('post_ort', 'like', "%{$request->post_ort}%");
        }

        // Filter by post_lan
        if ($request->has('post_lan')) {
            $query->where('post_lan', 'like', "%{$request->post_lan}%");
        }

        $perPage = min($request->get('per_page', 25), 100);
        $records = $query->paginate($perPage);

        return PostNummerApiResource::collection($records);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostNummerApiRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Upsert by post_nummer when provided
        if (! empty($data['post_nummer'])) {
            $existing = PostNummerApi::query()
                ->where('post_nummer', $data['post_nummer'])
                ->first();

            if ($existing) {
                $existing->update($data);

                return (new PostNummerApiResource($existing))
                    ->response()
                    ->setStatusCode(200);
            }
        }

        $record = PostNummerApi::create($data);

        return (new PostNummerApiResource($record))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PostNummerApi $postNummerApi): PostNummerApiResource
    {
        return new PostNummerApiResource($postNummerApi);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostNummerApiRequest $request, PostNummerApi $postNummerApi): PostNummerApiResource
    {
        $postNummerApi->update($request->validated());

        return new PostNummerApiResource($postNummerApi);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PostNummerApi $postNummerApi): JsonResponse
    {
        $postNummerApi->delete();

        return response()->json(['message' => 'Record deleted successfully'], 200);
    }
}
