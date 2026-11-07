<?php

namespace App\Observers;

use App\Models\Setting;
use App\Services\FrontendRevalidator;

class SettingObserver
{
    public function __construct(private FrontendRevalidator $revalidator) {}

    public function saved(Setting $setting): void
    {
        if ($setting->wasChanged('value') || $setting->wasRecentlyCreated) {
            $this->revalidator->queue('settings');
        }
    }
}
