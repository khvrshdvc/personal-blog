<?php

namespace App\Services\Activity;

interface ActivityProvider
{
    /**
     * @return array{real: bool, error: bool, login: ?string, days: list<array{d: string, c: int}>, runs: list<array<string, mixed>>, info: array<string, array<string, mixed>>}
     */
    public function get(): array;
}
