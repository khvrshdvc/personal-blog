<?php

namespace App\Services\Activity;

use Carbon\Carbon;

class DemoActivityProvider implements ActivityProvider
{
    private const RUNS = [
        ['erp-brainup', 'Tests', 'feat: kurslar uchun CRUD va validatsiya', 'main', 48, 'running', null, .01],
        ['blog-tulqinov', 'Deploy', 'feat: fikrlar va dasturlash boʻlimlari', 'main', 31, 'success', 96, .3],
        ['blog-tulqinov', 'Tests', 'feat: fikrlar va dasturlash boʻlimlari', 'main', 57, 'success', 74, .31],
        ['crm-system', 'Tests', 'fix: bitim summasini hisoblash', 'fix/deal-total', 112, 'failure', 131, 1.2],
        ['crm-system', 'Laravel Pint', 'fix: bitim summasini hisoblash', 'fix/deal-total', 89, 'success', 21, 1.21],
        ['erp-brainup', 'Tests', 'refactor: StudentService ajratildi', 'main', 47, 'success', 83, 2.1],
        ['telegram-task-bot', 'Lint', 'feat: kunlik vazifalar jadvali', 'main', 12, 'success', 18, 4],
        ['symfony-blog', 'CI', 'chore: bogʻliqliklarni yangilash', 'main', 9, 'skipped', null, 30],
    ];

    private const INFO = [
        'crm-system' => ['PHP', 2, 1.2],
        'erp-brainup' => ['PHP', 1, .02],
        'blog-tulqinov' => ['PHP', 0, .3],
        'symfony-blog' => ['PHP', 3, 62],
        'telegram-task-bot' => ['Python', 0, 4],
    ];

    public function get(): array
    {
        $now = now();
        $random = $this->random(20261105);

        $days = $this->gridDays(function (Carbon $date) use ($now, $random) {
            $age = $date->diffInSeconds($now, false) / 86400;
            $g = max(.08, 1 - $age / 400);
            $w = (($date->dayOfWeek + 6) % 7) >= 5 ? .5 : 1;

            return $random() < .1 + $g * .62 * $w
                ? 1 + (int) floor($random() * $random() * 11 * $g * $w + $random() * 2)
                : 0;
        });

        $runs = array_map(fn (array $r) => [
            'key' => $r[0],
            'repo' => $r[0],
            'workflow' => $r[1],
            'title' => $r[2],
            'branch' => $r[3],
            'num' => $r[4],
            'status' => $r[5],
            'dur' => $r[6],
            'at' => $now->copy()->subSeconds((int) round($r[7] * 86400)),
        ], self::RUNS);

        $info = [];
        foreach (self::INFO as $key => [$lang, $stars, $pushed]) {
            $info[$key] = [
                'lang' => $lang,
                'stars' => $stars,
                'pushed' => $now->copy()->subSeconds((int) round($pushed * 86400)),
                'url' => null,
            ];
        }

        return ['real' => false, 'login' => null, 'days' => $days, 'runs' => $runs, 'info' => $info, 'range' => 'soʻnggi yil'];
    }

    private function gridDays(callable $count): array
    {
        $end = now()->startOfDay();
        $offset = ($end->dayOfWeek + 6) % 7;
        $start = $end->copy()->subDays(52 * 7 + $offset);
        $out = [];

        for ($i = 0; $i < 371; $i++) {
            $date = $start->copy()->addDays($i);
            $out[] = $date->gt($end) ? null : ['d' => $date->toDateString(), 'c' => $count($date)];
        }

        return $out;
    }

    private function random(int $seed): \Closure
    {
        $a = $seed;

        return function () use (&$a): float {
            $a = ($a + 0x6D2B79F5) & 0xFFFFFFFF;
            $t = $this->imul($a ^ ($a >> 15), 1 | $a);
            $t = (($t + $this->imul($t ^ ($t >> 7), 61 | $t)) & 0xFFFFFFFF) ^ $t;

            return ((($t ^ ($t >> 14)) & 0xFFFFFFFF)) / 4294967296;
        };
    }

    private function imul(int $a, int $b): int
    {
        $a &= 0xFFFFFFFF;
        $b &= 0xFFFFFFFF;

        return ((($a & 0xFFFF) * $b) + (((($a >> 16) & 0xFFFF) * $b & 0xFFFF) << 16)) & 0xFFFFFFFF;
    }
}
