<?php

namespace App\Services\Activity;

use App\Models\Project;
use App\Services\SettingsService;
use App\Support\ResilientCache;
use Carbon\Carbon;

class ResilientActivityProvider implements ActivityProvider
{
    public function __construct(
        private SettingsService $settings,
        private GithubActivityProvider $github,
        private DemoActivityProvider $demo,
        private ResilientCache $cache,
    ) {
    }

    public function get(): array
    {
        $username = trim((string) $this->settings->get('github', ''));

        if ($username === '') {
            return $this->demo->get() + ['error' => false];
        }

        $repos = Project::whereNotNull('repo')->orderBy('id')->pluck('repo')->all();
        $key = 'activity:'.sha1(strtolower($username).'|'.implode(',', $repos));

        $raw = $this->cache->remember(
            $key,
            (int) config('services.github.fresh'),
            (int) config('services.github.stale'),
            fn () => $this->github->fetch($username, $repos),
        );

        if ($raw === null) {
            return array_replace($this->demo->get(), ['error' => true]);
        }

        return $this->hydrate($raw) + ['error' => false];
    }

    private function hydrate(array $raw): array
    {
        $raw['runs'] = array_map(fn (array $run) => array_replace($run, ['at' => Carbon::parse($run['at'])]), $raw['runs']);
        $raw['info'] = array_map(fn (array $info) => array_replace($info, [
            'pushed' => $info['pushed'] ? Carbon::parse($info['pushed']) : null,
        ]), $raw['info']);

        return $raw;
    }
}
