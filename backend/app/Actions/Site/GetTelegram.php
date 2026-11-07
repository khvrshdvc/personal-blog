<?php

namespace App\Actions\Site;

use App\Services\SettingsService;
use App\Services\TelegramChannel;

class GetTelegram
{
    public function __construct(
        private SettingsService $settings,
        private TelegramChannel $channel,
    ) {}

    public function __invoke(): array
    {
        $saved = (array) $this->settings->get('telegram', []);
        $username = trim((string) ($saved['u'] ?? ''));
        $manual = array_values(array_filter(
            (array) ($saved['posts'] ?? []),
            fn ($post) => is_array($post) && trim((string) ($post['t'] ?? '')) !== '',
        ));

        $posts = $username === '' ? null : $this->channel->latestPosts($username);

        return [
            'u' => $username,
            'n' => (string) ($saved['n'] ?? ''),
            'posts' => array_map(
                fn (array $post) => ['t' => (string) $post['t'], 'd' => (string) ($post['d'] ?? '')],
                $posts ?? $manual,
            ),
        ];
    }
}
