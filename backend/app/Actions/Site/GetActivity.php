<?php

namespace App\Actions\Site;

use App\Services\Activity\ActivityProvider;
use App\Support\ActivityDays;

class GetActivity
{
    public function __construct(private ActivityProvider $provider) {}

    public function __invoke(): array
    {
        $data = $this->provider->get();

        return [
            'days' => $data['days'],
            'total' => ActivityDays::total($data['days']),
            'real' => $data['real'],
        ];
    }
}
