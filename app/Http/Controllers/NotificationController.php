<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index(): JsonResponse
    {
        $notifications = $this->notificationService->getForUser(auth()->id());

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    public function markRead(int $id): JsonResponse
    {
        $this->notificationService->markRead($id, auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Notification marquee comme lue'
        ]);
    }

    public function markAllRead(): JsonResponse
    {
        $this->notificationService->markAllRead(auth()->id());

        return response()->json([
            'success' => true,
            'message' => 'Toutes les notifications marquees comme lues'
        ]);
    }
}
