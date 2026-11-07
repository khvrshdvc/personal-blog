<?php

namespace App\Services;

use App\Models\Project;
use App\Services\Activity\GithubClient;
use App\Support\RepoName;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GithubProjectSync
{
    public const USERNAME = '/^[A-Za-z0-9](?:[A-Za-z0-9-]{0,38})$/';

    public function __construct(
        private SettingsService $settings,
        private GithubClient $client,
    ) {}

    public function sync(): array
    {
        $username = trim((string) $this->settings->get('github', ''));

        if (! preg_match(self::USERNAME, $username)) {
            throw ValidationException::withMessages(['github' => 'Avval sozlamalarda GitHub username kiriting.']);
        }

        $repos = $this->client->userRepositories($username);

        if ($repos === null) {
            throw new RuntimeException('GitHub javob bermadi.');
        }

        $hidden = array_map('strtolower', (array) $this->settings->get('github_hidden', []));
        $existing = Project::query()->whereNotNull('repo')->get()->keyBy(fn (Project $project) => $this->key($project->repo));
        $created = 0;
        $updated = 0;

        foreach ($repos as $repo) {
            $name = (string) ($repo['name'] ?? '');

            if ($name === '' || ($repo['fork'] ?? false) || ($repo['archived'] ?? false) || strcasecmp($name, $username) === 0) {
                continue;
            }

            $key = strtolower($name);
            $data = $this->map($repo);

            if ($project = $existing->get($key)) {
                $fill = array_filter([
                    'description' => $project->description ? null : $data['description'],
                    'stack' => $project->stack ? null : $data['stack'],
                    'demo_url' => $project->demo_url ? null : $data['demo_url'],
                ], fn ($value) => $value !== null && $value !== []);

                if ($fill !== []) {
                    $project->update($fill);
                    $updated++;
                }

                continue;
            }

            if (in_array($key, $hidden, true)) {
                continue;
            }

            Project::create($data);
            $created++;
        }

        return ['created' => $created, 'updated' => $updated, 'total' => Project::count()];
    }

    private function map(array $repo): array
    {
        $topics = array_map(fn ($topic) => ucfirst((string) $topic), (array) ($repo['topics'] ?? []));
        $stack = collect([$repo['language'] ?? null, ...$topics])
            ->filter(fn ($item) => is_string($item) && trim($item) !== '')
            ->map(fn (string $item) => mb_substr(trim($item), 0, 40))
            ->unique(fn (string $item) => mb_strtolower($item))
            ->take(8)
            ->values()
            ->all();

        return [
            'name' => mb_substr((string) $repo['name'], 0, 120),
            'repo' => (string) ($repo['full_name'] ?? $repo['name']),
            'description' => ($description = trim((string) ($repo['description'] ?? ''))) !== '' ? mb_substr($description, 0, 220) : null,
            'stack' => $stack,
            'demo_url' => $this->homepage((string) ($repo['homepage'] ?? '')),
        ];
    }

    private function homepage(string $value): ?string
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (! preg_match('#^https?://#i', $value)) {
            $value = 'https://'.$value;
        }

        return filter_var($value, FILTER_VALIDATE_URL) && mb_strlen($value) <= 255 ? $value : null;
    }

    private function key(?string $repo): string
    {
        return strtolower(last(explode('/', RepoName::normalize($repo))));
    }
}
