<?php

namespace App\View\Composers;

use App\Enums\PostCategory;
use App\Services\SettingsService;
use App\Services\SiteStats;
use Illuminate\View\View;

class NavComposer
{
    public function __construct(
        private SettingsService $settings,
        private SiteStats $stats,
    ) {
    }

    public function compose(View $view): void
    {
        $view->with([
            'navItems' => [
                ['label' => 'Asosiy', 'icon' => 'grid', 'href' => route('home'), 'match' => ['home'], 'count' => null],
                ['label' => 'Fikrlar', 'icon' => 'bookOpen', 'href' => route('fikrlar'), 'match' => ['fikrlar'], 'count' => $this->stats->postCount(PostCategory::Fikr)],
                ['label' => 'Dasturlash', 'icon' => 'code', 'href' => route('dasturlash'), 'match' => ['dasturlash', 'yozuvlar', 'loyihalar', 'faollik'], 'count' => $this->stats->postCount(PostCategory::Dev)],
                ['label' => 'Men haqimda', 'icon' => 'person', 'href' => route('men'), 'match' => ['men'], 'count' => null],
            ],
            'isOpen' => (bool) $this->settings->get('open', true),
        ]);
    }
}
