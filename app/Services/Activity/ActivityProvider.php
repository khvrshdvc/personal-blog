<?php

namespace App\Services\Activity;

interface ActivityProvider
{
    /**
     * @return array{real: bool, login: ?string, days: list<array{d: string, c: int}|null>, runs: list<array<string, mixed>>, info: array<string, array<string, mixed>>, range: string}
     */
    public function get(): array;
}
