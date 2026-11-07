<?php

namespace App\Actions\Admin;

use App\Services\GithubProjectSync;
use App\Services\SettingsService;
use App\Support\RepoName;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateSettings
{
    public function __construct(
        private SettingsService $settings,
        private GithubProjectSync $projects,
    ) {}

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

        $previous = (string) $this->settings->get('github', '');

        foreach ($values as $key => $value) {
            $this->settings->put($key, $value);
        }

        if ($values['github'] !== '' && strcasecmp($values['github'], $previous) !== 0) {
            try {
                $this->projects->sync();
            } catch (Throwable $e) {
                Log::warning('GitHub loyihalarini yuklab boʻlmadi', ['error' => class_basename($e)]);
            }
        }

        return $values;
    }
}
