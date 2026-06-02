<?php

namespace App\Http\Controllers;

use App\Models\TrainingRegistration;
use App\Models\TrainingSession;
use App\Support\TrainingRegistrationOverlap;
use Illuminate\Http\Request;

class TrainingRegistrationController extends Controller
{
    /**
     * Register student for training session.
     */
    public function register(Request $request, TrainingSession $trainingSession)
    {
        $user = auth()->user();

        // Проверяем, что пользователь - студент
        if ($user->role !== 'student') {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Только студенты могут регистрироваться на тренировки.');
        }

        // Проверяем, что тренировка запланирована (можно регистрироваться только на запланированные)
        if ($trainingSession->status !== 'scheduled') {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Регистрация доступна только для запланированных тренировок.');
        }

        // Проверяем, не зарегистрирован ли уже
        $existingRegistration = TrainingRegistration::where('training_id', $trainingSession->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Вы уже зарегистрированы на эту тренировку.');
        }

        if (TrainingRegistrationOverlap::hasConflict($user, $trainingSession)) {
            return redirect()->route('training-sessions.show', $trainingSession);
        }

        // Регистрируем
        TrainingRegistration::create([
            'training_id' => $trainingSession->id,
            'user_id' => $user->id,
            'registered_at' => now(),
        ]);

        return redirect()->route('training-sessions.show', $trainingSession)
            ->with('success', 'Вы успешно зарегистрированы на тренировку!');
    }

    /**
     * Unregister student from training session.
     */
    public function unregister(Request $request, TrainingSession $trainingSession)
    {
        $user = auth()->user();

        // Проверяем, что пользователь - студент
        if ($user->role !== 'student') {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Только студенты могут отменять регистрацию.');
        }

        // Проверяем, что тренировка запланирована (можно отменять регистрацию только на запланированных)
        if ($trainingSession->status !== 'scheduled') {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Отмена регистрации доступна только для запланированных тренировок.');
        }

        // Находим регистрацию
        $registration = TrainingRegistration::where('training_id', $trainingSession->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$registration) {
            return redirect()->route('training-sessions.show', $trainingSession)
                ->with('error', 'Вы не зарегистрированы на эту тренировку.');
        }

        // Удаляем регистрацию
        $registration->delete();

        return redirect()->route('training-sessions.show', $trainingSession)
            ->with('success', 'Регистрация на тренировку отменена.');
    }
}
