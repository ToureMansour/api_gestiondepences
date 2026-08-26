<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key_name' => 'organization_name', 'value' => 'Depensys'],
            ['key_name' => 'notifications_enabled', 'value' => 'true'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key_name' => $setting['key_name']],
                ['value' => $setting['value']]
            );
        }
    }
}
