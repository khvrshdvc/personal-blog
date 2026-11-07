<?php

namespace App\Actions\Admin;

use App\Services\SettingsService;
use App\Support\RepoName;

class UpdateSettings
{
    public function __construct(private SettingsService $settings) {}

    public function __invoke(array $input): array
    {
        $values = [
            'bio' => (string) ($input['bio'] ?? ''),
            'open' => (bool) $input['open'],
            'github' => RepoName::username($input['github'] ?? ''),
            'now' => array_map(fn ($n) => [
                'k' => trim((string) ($n['k'] ?? '')),
                'v' => trim((string) ($n['v'] ?? '')),
            ], array_values($input['now'])),
            'learn' => array_map(fn ($l) => [
                'n' => trim((string) ($l['n'] ?? '')),
                'p' => max(0, min(100, (int) ($l['p'] ?? 0))),
            ], array_values($input['learn'])),
            'telegram' => [
                'u' => RepoName::telegram($input['telegram']['u'] ?? ''),
                'n' => trim((string) ($input['telegram']['n'] ?? '')),
                'posts' => array_map(fn ($p) => [
                    't' => trim((string) ($p['t'] ?? '')),
                    'd' => (string) ($p['d'] ?? ''),
                ], array_values($input['telegram']['posts'])),
            ],
        ];

        foreach ($values as $key => $value) {
            $this->settings->put($key, $value);
        }

        return $values;
    }
}
