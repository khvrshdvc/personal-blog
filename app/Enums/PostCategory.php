<?php

namespace App\Enums;

enum PostCategory: string
{
    case Fikr = 'fikr';
    case Dev = 'dev';

    public function label(): string
    {
        return match ($this) {
            self::Fikr => 'Fikrlar',
            self::Dev => 'Dasturlash',
        };
    }
}
