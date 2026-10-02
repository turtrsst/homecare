<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\NotificationResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** GET /api/v1/notifications */
    public function index(Request $request): JsonResponse
    {
        $paginator = $request->user()->notifications()->paginate(20);

        $paginator->setCollection(
            collect(NotificationResource::collection($paginator->getCollection())->resolve())
        );

        return ApiResponse::paginated($paginator, 'Notifikasi Anda');
    }

    /** POST /api/v1/notifications/{id}/read */
    public function markRead(Request $request, string $notification): JsonResponse
    {
        $item = $request->user()->notifications()->findOrFail($notification);
        $item->markAsRead();

        return ApiResponse::success(null, 'Notifikasi ditandai dibaca');
    }
}
