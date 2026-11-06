<?php

namespace App\Actions;

use App\Enums\PostCategory;
use App\Models\Post;
use App\Models\Project;
use App\Services\SettingsService;

class GetMenData
{
    public function __construct(private SettingsService $settings)
    {
    }

    public function __invoke(): array
    {
        $now = collect($this->settings->get('now', []))
            ->filter(fn ($n) => trim($n['k'] ?? '') !== '' || trim($n['v'] ?? '') !== '')
            ->values()
            ->all();

        $bio = trim((string) $this->settings->get('bio', ''));
        $paragraphs = $bio === '' ? [] : array_map('trim', preg_split('/\n\s*\n/', $bio));

        return [
            'isOpen' => (bool) $this->settings->get('open', true),
            'now' => $now,
            'paragraphs' => $paragraphs,
            'github' => trim((string) $this->settings->get('github', '')),
            'telegram' => trim((string) ($this->settings->get('telegram', [])['u'] ?? '')),
            'projectCount' => Project::count(),
            'thoughtCount' => Post::published()->inCategory(PostCategory::Fikr)->count(),
        ];
    }
}
