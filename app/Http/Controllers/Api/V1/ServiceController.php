<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ServiceResource;
use App\Models\HomecareService;
use App\Services\HomecareCatalog;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function __construct(private readonly HomecareCatalog $catalog) {}

    /** GET /api/v1/services — katalog layanan aktif. */
    public function index(): JsonResponse
    {
        return ApiResponse::success(
            ServiceResource::collection($this->catalog->activeServices())->resolve(),
        );
    }

    /** GET /api/v1/services/{slug} */
    public function show(HomecareService $service): JsonResponse
    {
        abort_unless($service->is_active, 404);

        return ApiResponse::success(new ServiceResource($service));
    }
}
