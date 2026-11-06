<?php

namespace App\Support;

class ActivityStats
{
    public static function summarize(array $days): array
    {
        $days = array_values(array_filter($days));
        $total = 0;
        $current = 0;

        foreach ($days as $day) {
            $total += $day['c'];
        }

        for ($i = count($days) - 1; $i >= 0; $i--) {
            if ($days[$i]['c'] > 0) {
                $current++;
            } elseif ($i === count($days) - 1) {
                continue;
            } else {
                break;
            }
        }

        return ['total' => $total, 'streak' => $current];
    }

    public static function gridDays(callable $count, int $limit = 371): array
    {
        $end = now()->startOfDay();
        $offset = ($end->dayOfWeek + 6) % 7;
        $start = $end->copy()->subDays(52 * 7 + $offset);
        $out = [];

        for ($i = 0; $i < 371; $i++) {
            $date = $start->copy()->addDays($i);
            $out[] = $date->gt($end) ? null : ['d' => $date->toDateString(), 'c' => $count($date)];
        }

        return array_slice($out, -$limit);
    }

    public static function level(int $count): int
    {
        return match (true) {
            $count <= 0 => 0,
            $count <= 2 => 1,
            $count <= 5 => 2,
            $count <= 9 => 3,
            default => 4,
        };
    }
}
