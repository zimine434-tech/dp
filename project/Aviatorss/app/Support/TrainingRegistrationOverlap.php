<?php

namespace App\Support;

use App\Models\TrainingRegistration;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class TrainingRegistrationOverlap
{
    public static function findConflictingRegistration(User $user, TrainingSession $session): ?TrainingRegistration
    {
        return TrainingRegistration::query()
            ->where('user_id', $user->id)
            ->where('training_id', '!=', $session->id)
            ->whereHas('training', function (Builder $q) use ($session) {
                $q->where('status', 'scheduled')
                    ->where('start_time', '<', $session->end_time)
                    ->where('end_time', '>', $session->start_time);
            })
            ->with('training')
            ->first();
    }

    public static function hasConflict(User $user, TrainingSession $session): bool
    {
        return self::findConflictingRegistration($user, $session) !== null;
    }

    public static function conflictMessage(TrainingSession $conflictingSession): string
    {
        $start = $conflictingSession->start_time;
        $end = $conflictingSession->end_time;

        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            $time = $start->format('d.m.Y').', '.$start->format('H:i').' – '.$end->format('H:i');
        } else {
            $time = $start->format('d.m.Y H:i').' – '.$end->format('d.m.Y H:i');
        }

        return 'Вы уже записаны на тренировку «'.$conflictingSession->title.'» в это же время ('.$time.').';
    }
}
