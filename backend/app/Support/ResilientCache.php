<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResilientCache
{
    private const FAILURE_PAUSE = 60;

    public function remember(string $key, int $fresh, int $stale, Closure $fetch): mixed
    {
        $cached = Cache::get($key.':fresh');

        if ($cached !== null) {
            return $cached;
        }

        if (Cache::has($key.':failed')) {
            return Cache::get($key.':stale');
        }

        try {
            $value = $fetch();
        } catch (Throwable $e) {
            Log::warning('Tashqi manba javob bermadi', ['key' => $key, 'error' => class_basename($e)]);
            $value = null;
        }

        if ($value === null) {
            Cache::put($key.':failed', true, self::FAILURE_PAUSE);

            return Cache::get($key.':stale');
        }

        Cache::put($key.':fresh', $value, $fresh);
        Cache::put($key.':stale', $value, $stale);
        Cache::forget($key.':failed');

        return $value;
    }
}
