<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class Uz
{
    private const SHORT = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];

    private const MONTHS = ['yanvar', 'fevral', 'mart', 'aprel', 'may', 'iyun', 'iyul', 'avgust', 'sentabr', 'oktabr', 'noyabr', 'dekabr'];

    public static function short(CarbonInterface|string|null $date): string
    {
        $date = self::parse($date);

        if ($date === null) {
            return '';
        }

        return $date->day.' '.mb_strtolower(self::SHORT[$date->month - 1]);
    }

    public static function long(CarbonInterface|string|null $date): string
    {
        $date = self::parse($date);

        if ($date === null) {
            return '';
        }

        return $date->day.'-'.self::MONTHS[$date->month - 1].', '.$date->year;
    }

    public static function ago(CarbonInterface $date): string
    {
        $seconds = max(0, (int) $date->diffInSeconds(now(), false));

        if ($seconds < 60) {
            return 'hozirgina';
        }

        $minutes = intdiv($seconds, 60);
        if ($minutes < 60) {
            return $minutes.' daqiqa oldin';
        }

        $hours = intdiv($minutes, 60);
        if ($hours < 24) {
            return $hours.' soat oldin';
        }

        $days = intdiv($hours, 24);
        if ($days < 7) {
            return $days.' kun oldin';
        }
        if ($days < 30) {
            return intdiv($days, 7).' hafta oldin';
        }
        if ($days < 365) {
            return intdiv($days, 30).' oy oldin';
        }

        return intdiv($days, 365).' yil oldin';
    }

    public static function initials(?string $name): string
    {
        $words = array_values(array_filter(preg_split('/[^A-Za-z0-9ʻʼ\']+/u', (string) $name)));
        $first = $words[0] ?? '?';
        $second = isset($words[1]) ? mb_substr($words[1], 0, 1) : mb_substr($first, 1, 1);

        return mb_strtoupper(mb_substr($first, 0, 1).$second);
    }

    private static function parse(CarbonInterface|string|null $date): ?CarbonInterface
    {
        if ($date === null || $date === '') {
            return null;
        }

        if ($date instanceof CarbonInterface) {
            return $date;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }
}
