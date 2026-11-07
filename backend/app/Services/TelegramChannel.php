<?php

namespace App\Services;

use App\Support\ResilientCache;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Support\Facades\Http;

class TelegramChannel
{
    private const LIMIT = 2;

    public function __construct(private ResilientCache $cache) {}

    public function latestPosts(string $username): ?array
    {
        if (! preg_match('/^[A-Za-z][A-Za-z0-9_]{3,31}$/', $username)) {
            return null;
        }

        return $this->cache->remember(
            'telegram:'.strtolower($username),
            (int) config('services.telegram.fresh'),
            (int) config('services.telegram.stale'),
            fn () => $this->fetch($username),
        );
    }

    private function fetch(string $username): ?array
    {
        $html = Http::withHeaders(['Accept' => 'text/html', 'User-Agent' => 'personal-blog'])
            ->timeout((int) config('services.telegram.timeout'))
            ->get('https://t.me/s/'.$username)
            ->throw()
            ->body();

        $posts = $this->parse($html);

        return $posts === [] ? null : $posts;
    }

    public function parse(string $html): array
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $xpath = new DOMXPath($dom);
        $posts = [];

        foreach ($xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' tgme_widget_message ') and @data-post]") as $message) {
            $textNode = $xpath->query(".//div[contains(@class, 'tgme_widget_message_text')]", $message)->item(0);

            if ($textNode === null) {
                continue;
            }

            foreach ($xpath->query('.//br', $textNode) as $br) {
                $br->parentNode->replaceChild($dom->createTextNode(' '), $br);
            }

            $text = trim(preg_replace('/\s+/u', ' ', $textNode->textContent));

            if ($text === '') {
                continue;
            }

            $time = $xpath->query('.//time/@datetime', $message)->item(0);

            $posts[] = [
                't' => mb_strimwidth($text, 0, 300, '…'),
                'd' => $time ? Carbon::parse($time->nodeValue)->timezone(config('app.timezone'))->toDateString() : '',
            ];
        }

        return array_slice($posts, -self::LIMIT);
    }
}
