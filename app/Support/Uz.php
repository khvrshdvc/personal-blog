<?php

namespace App\Support;

use Carbon\CarbonInterface;

class Uz
{
    private const SHORT = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyn', 'Iyl', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek'];

    public static function short(?CarbonInterface $date): string
    {
        if ($date === null) {
            return '';
        }

        return $date->day.' '.mb_strtolower(self::SHORT[$date->month - 1]);
    }
}
