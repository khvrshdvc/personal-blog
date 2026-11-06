<?php

namespace App\Services;

use App\Models\Setting;

class SettingsService
{
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::find($key);

        return $setting === null ? $default : $setting->value;
    }

    public function put(string $key, mixed $value): void
    {
        Setting::updateOrCreate(['key' => $key], ['value' => $value]);
    }

    public function all(): array
    {
        return Setting::all()->mapWithKeys(fn (Setting $s) => [$s->key => $s->value])->all();
    }
}
