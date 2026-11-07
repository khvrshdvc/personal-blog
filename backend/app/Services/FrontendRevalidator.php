<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FrontendRevalidator
{
    private array $tags = [];

    private bool $scheduled = false;

    public function enabled(): bool
    {
        return (string) config('services.frontend.url') !== '' && (string) config('services.frontend.secret') !== '';
    }

    public function queue(string ...$tags): void
    {
        if (! $this->enabled()) {
            return;
        }

        $this->tags = array_values(array_unique([...$this->tags, ...array_filter($tags)]));

        if (! $this->scheduled) {
            $this->scheduled = true;
            app()->terminating(fn () => $this->flush());
        }
    }

    public function flush(): void
    {
        $tags = $this->tags;
        $this->tags = [];
        $this->scheduled = false;

        if ($tags === [] || ! $this->enabled()) {
            return;
        }

        try {
            Http::timeout((int) config('services.frontend.timeout'))
                ->acceptJson()
                ->post(rtrim((string) config('services.frontend.url'), '/').'/api/revalidate', [
                    'secret' => config('services.frontend.secret'),
                    'tags' => $tags,
                ])
                ->throw();
        } catch (Throwable $e) {
            Log::warning('Frontend keshini yangilab boʻlmadi', ['tags' => $tags, 'error' => class_basename($e)]);
        }
    }
}
