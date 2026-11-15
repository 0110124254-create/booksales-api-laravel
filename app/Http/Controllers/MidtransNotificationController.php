<?php

namespace App\Http\Controllers;

use App\Services\MidtransSnap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransSnap $midtrans): JsonResponse
    {
        if (! $midtrans->handleNotification($request->all())) {
            return response()->json(['message' => 'Invalid notification'], 401);
        }

        return response()->json(['message' => 'Notification processed']);
    }
}
