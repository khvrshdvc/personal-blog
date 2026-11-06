<?php

namespace App\Services\Activity;

interface ActivityProvider
{
    /**
     * @return array{days: list<array{d: string, c: int}|null>, runs: list<array<string, mixed>>, range: string}
     */
    public function get(): array;
}
