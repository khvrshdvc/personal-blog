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
