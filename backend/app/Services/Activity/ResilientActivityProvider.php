<?php

namespace App\Services\Activity;

use App\Models\Project;
use App\Services\SettingsService;
use App\Support\ResilientCache;

class ResilientActivityProvider implements ActivityProvider
{
    private const USERNAME = '/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/';

    public function __construct(
        private SettingsService $settings,
        private GithubActivityProvider $github,
        private DemoActivityProvider $demo,
        private ResilientCache $cache,
    ) {}

    public function get(): array
    {
        $username = trim((string) $this->settings->get('github', ''));

        if ($username === '' || ! preg_match(self::USERNAME, $username)) {
            return $this->demo->get();
        }

        $repos = Project::whereNotNull('repo')->orderBy('id')->pluck('repo')->all();
        $key = 'activity:'.sha1(strtolower($username).'|'.implode(',', $repos));

        $data = $this->cache->remember(
            $key,
            (int) config('services.github.fresh'),
            (int) config('services.github.stale'),
            fn () => $this->github->fetch($username, $repos),
        );

        if ($data === null) {
            return array_replace($this->demo->get(), ['error' => true]);
        }

        return $data;
    }
}
