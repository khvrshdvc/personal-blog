<?php

namespace App\Services;

use App\Support\ResilientCache;
use Carbon\Carbon;
use DOMDocument;
use DOMXPath;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;

class TelegramChannel
{
    private const USERNAME = '/^[A-Za-z][A-Za-z0-9_]{3,31}$/';

    private const LIMIT = 2;

    private const FEED_LIMIT = 100;

    private const FEED_PAGES = 5;

    public function __construct(private ResilientCache $cache) {}

    public function latestPosts(string $username): ?array
    {
        if (! preg_match(self::USERNAME, $username)) {
            return null;
        }

        return $this->cache->remember(
            'telegram:'.strtolower($username),
            (int) config('services.telegram.fresh'),
            (int) config('services.telegram.stale'),
            fn () => $this->fetch($username),
        );
    }

    public function feed(string $username): ?array
    {
        if (! preg_match(self::USERNAME, $username)) {
            return null;
        }

        return $this->cache->remember(
            'telegram-feed:'.strtolower($username),
            (int) config('services.telegram.fresh'),
            (int) config('services.telegram.stale'),
            function () use ($username) {
                $posts = [];
                $before = null;

                for ($page = 0; $page < self::FEED_PAGES; $page++) {
                    try {
                        $html = $this->page($username, $before);
                    } catch (ConnectionException|RequestException $exception) {
                        if ($page === 0) {
                            throw $exception;
                        }

                        break;
                    }

                    $posts = array_merge($posts, $this->parseFeed($html, $username));
                    $ids = $this->messageIds($html);

                    if ($ids === [] || min($ids) <= 1) {
                        break;
                    }

                    $before = min($ids);
                }

                usort($posts, fn (array $a, array $b) => $b['id'] <=> $a['id']);

                return $posts === [] ? null : array_slice($posts, 0, self::FEED_LIMIT);
            },
        );
    }

    private function messageIds(string $html): array
    {
        preg_match_all('/data-post="[^"\/]+\/(\d+)"/', $html, $matches);

        return array_map('intval', $matches[1]);
    }

    private function page(string $username, ?int $before = null): string
    {
        return Http::withHeaders(['Accept' => 'text/html', 'User-Agent' => 'personal-blog'])
            ->timeout((int) config('services.telegram.timeout'))
            ->get('https://t.me/s/'.$username, $before === null ? [] : ['before' => $before])
            ->throw()
            ->body();
    }

    public function parseFeed(string $html, string $username): array
    {
        $dom = $this->load($html);
        $xpath = new DOMXPath($dom);
        $posts = [];

        foreach ($xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' tgme_widget_message ') and @data-post]") as $message) {
            $textNode = $xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' js-message_text ')]", $message)->item(0);
            $id = (int) substr((string) strrchr($message->getAttribute('data-post'), '/'), 1);

            if ($textNode === null || $id < 1) {
                continue;
            }

            foreach ($xpath->query('.//br', $textNode) as $br) {
                $br->parentNode->replaceChild($dom->createTextNode("\n"), $br);
            }

            $text = trim(preg_replace("/[ \t]+/u", ' ', preg_replace("/\n{3,}/", "\n\n", str_replace("\r", '', $textNode->textContent))));

            if ($text === '') {
                continue;
            }

            $time = $xpath->query('.//time/@datetime', $message)->item(0);

            $posts[] = [
                'id' => $id,
                't' => $text,
                'd' => $time ? Carbon::parse($time->nodeValue)->timezone(config('app.timezone'))->toDateString() : '',
                'url' => 'https://t.me/'.$username.'/'.$id,
            ];
        }

        return array_reverse($posts);
    }

    private function load(string $html): DOMDocument
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return $dom;
    }

    private function fetch(string $username): ?array
    {
        $posts = $this->parse($this->page($username));

        return $posts === [] ? null : $posts;
    }

    public function parse(string $html): array
    {
        $dom = $this->load($html);
        $xpath = new DOMXPath($dom);
        $posts = [];

        foreach ($xpath->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' tgme_widget_message ') and @data-post]") as $message) {
            $textNode = $xpath->query(".//div[contains(concat(' ', normalize-space(@class), ' '), ' js-message_text ')]", $message)->item(0);

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
