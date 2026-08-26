<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $settings = Setting::pluck('value', 'key_name')->toArray();

        $data = [];
        foreach ($settings as $key => $value) {
            $data[$key] = match ($value) {
                'true' => true,
                'false' => false,
                default => $value,
            };
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key_name' => $key],
                ['value' => is_bool($value) ? ($value ? 'true' : 'false') : $value]
            );
        }

        $settings = Setting::pluck('value', 'key_name')->toArray();
        $response = [];
        foreach ($settings as $key => $value) {
            $response[$key] = match ($value) {
                'true' => true,
                'false' => false,
                default => $value,
            };
        }

        return response()->json([
            'success' => true,
            'message' => 'Parametres enregistres',
            'data' => $response
        ]);
    }
}
