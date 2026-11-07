<?php

namespace App\Actions\Site;

use App\Services\SettingsService;
use App\Services\TelegramChannel;

class GetTelegramPosts
{
    public function __construct(
        private SettingsService $settings,
        private TelegramChannel $channel,
    ) {}

    public function __invoke(): array
    {
        $saved = (array) $this->settings->get('telegram', []);
        $username = trim((string) ($saved['u'] ?? ''));

        return [
            'u' => $username,
            'n' => (string) ($saved['n'] ?? ''),
            'posts' => $username === '' ? [] : ($this->channel->feed($username) ?? []),
        ];
    }
}
