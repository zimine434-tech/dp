<?php

namespace App\Observers;

use App\Models\TrainingSession;
use App\Services\MaxBotTrainingNotifier;

class TrainingSessionObserver
{
    public function __construct(
        protected MaxBotTrainingNotifier $maxBotTrainingNotifier,
    ) {}

    public function created(TrainingSession $trainingSession): void
    {
        \Log::info('MAX bot: TrainingSession created', [
            'id' => $trainingSession->id,
            'sport_id' => $trainingSession->sport_id,
            'status' => $trainingSession->status,
        ]);

        if ($trainingSession->status !== 'scheduled') {
            return;
        }

        $this->maxBotTrainingNotifier->notifyNewScheduledTraining($trainingSession);
    }
}
