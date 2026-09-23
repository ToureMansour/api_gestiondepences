<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;

class SettingsController extends Controller
{
    private const LOGO_DISK = 'public';
    private const LOGO_FOLDER = 'logos';

    public function index(): JsonResponse
    {
        $settings = Setting::pluck('value', 'key_name')->toArray();

        $data = [];
        foreach ($settings as $key => $value) {
            $data[$key] = $this->formatValue($key, $value);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'organization_name' => 'nullable|string|max:255',
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $data = $request->all();

        foreach ($data as $key => $value) {
            if ($key === 'logo') {
                continue;
            }

            Setting::updateOrCreate(
                ['key_name' => $key],
                ['value' => is_bool($value) ? ($value ? 'true' : 'false') : $value]
            );
        }

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            if ($logo instanceof UploadedFile && $logo->isValid()) {
                $path = $this->storeLogo($logo);
                Setting::updateOrCreate(
                    ['key_name' => 'logo'],
                    ['value' => $path]
                );
            }
        }

        $settings = Setting::pluck('value', 'key_name')->toArray();
        $response = [];
        foreach ($settings as $key => $value) {
            $response[$key] = $this->formatValue($key, $value);
        }

        return response()->json([
            'success' => true,
            'message' => 'Parametres enregistres',
            'data' => $response
        ]);
    }

    private function storeLogo(UploadedFile $file): string
    {
        $filename = uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs(self::LOGO_FOLDER, $filename, self::LOGO_DISK);
    }

    private function formatValue(string $key, ?string $value): mixed
    {
        if ($key === 'logo') {
            return $value ? asset('storage/' . $value) : null;
        }

        return match ($value) {
            'true' => true,
            'false' => false,
            default => $value,
        };
    }
}