<?php

namespace App\Support;

class RunStatus
{
    private const CI = [
        'success' => 'CI oʻtdi',
        'failure' => 'CI xato',
        'running' => 'CI ishlamoqda',
        'skipped' => 'CI oʻtkazildi',
    ];

    private const LABELS = [
        'success' => 'Muvaffaqiyatli',
        'failure' => 'Xato',
        'running' => 'Ishlamoqda',
        'skipped' => 'Oʻtkazib yuborildi',
    ];

    public static function ci(string $status): string
    {
        return self::CI[$status] ?? '';
    }

    public static function label(string $status): string
    {
        return self::LABELS[$status] ?? '';
    }
}
