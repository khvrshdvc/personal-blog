<?php

namespace App\Actions\Site;

use App\Services\SettingsService;

class GetPublicSettings
{
    public const KEYS = ['github', 'open', 'bio', 'now', 'learn', 'telegram'];

    public function __construct(private SettingsService $settings) {}

    public function __invoke(): array
    {
        $all = $this->settings->all();

        return collect(self::KEYS)
            ->mapWithKeys(fn (string $key) => [$key => $all[$key] ?? null])
            ->all();
    }
}
