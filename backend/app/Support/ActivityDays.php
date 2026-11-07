<?php

namespace App\Support;

use Carbon\Carbon;

class ActivityDays
{
    public const DAYS = 60;

    public static function range(callable $count): array
    {
        $today = now()->startOfDay();
        $days = [];

        for ($i = self::DAYS - 1; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $days[] = ['d' => $date->toDateString(), 'c' => max(0, (int) $count($date))];
        }

        return $days;
    }

    public static function total(array $days): int
    {
        return array_sum(array_column($days, 'c'));
    }

    public static function counts(callable $resolver): array
    {
        return self::range(fn (Carbon $date) => $resolver($date->toDateString()));
    }
}
