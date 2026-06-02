<?php

namespace App\Observers;

use App\Models\Competition;
use App\Services\MaxBotTrainingNotifier;

class CompetitionObserver
{
    public function __construct(
        protected MaxBotTrainingNotifier $maxBotTrainingNotifier,
    ) {}

    public function created(Competition $competition): void
    {
        \Log::info('MAX bot: Competition created', [
            'id' => $competition->id,
            'sport_id' => $competition->sport_id,
            'status' => $competition->status,
        ]);

        // In this project, new competitions are usually created as "upcoming"
        if ($competition->status !== 'upcoming') {
            return;
        }

        $this->maxBotTrainingNotifier->notifyNewUpcomingCompetition($competition);
    }
}

