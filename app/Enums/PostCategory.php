<?php

namespace App\Enums;

enum PostCategory: string
{
    case Fikr = 'fikr';
    case Dev = 'dev';

    public function one(): string
    {
        return match ($this) {
            self::Fikr => 'Fikr',
            self::Dev => 'Dasturlash',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Fikr => 'Fikrlar',
            self::Dev => 'Dasturlash',
        };
    }
}
