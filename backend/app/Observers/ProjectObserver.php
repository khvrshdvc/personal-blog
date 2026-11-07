<?php

namespace App\Observers;

use App\Models\Project;
use App\Services\FrontendRevalidator;

class ProjectObserver
{
    public function __construct(private FrontendRevalidator $revalidator) {}

    public function saved(Project $project): void
    {
        $this->revalidator->queue('projects');
    }

    public function deleted(Project $project): void
    {
        $this->revalidator->queue('projects');
    }
}
