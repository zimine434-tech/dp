<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MaxBotSubscriber;
use App\Models\Sport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MaxBotSubscriptionController extends Controller
{
    private const SCOPES = ['training', 'competition'];

    private function normalizeScope(?string $scope): string
    {
        $scope = is_string($scope) ? strtolower(trim($scope)) : '';
        return in_array($scope, self::SCOPES, true) ? $scope : 'training';
    }

    private function columnByScope(string $scope): string
    {
        return $scope === 'competition' ? 'competition_sport_ids' : 'training_sport_ids';
    }

    public function sports(): JsonResponse
    {
        $rows = Sport::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $rows]);
    }

    public function show(int $max_user_id): JsonResponse
    {
        $scope = $this->normalizeScope(request()->query('scope'));
        $column = $this->columnByScope($scope);

        $subscriber = MaxBotSubscriber::query()
            ->where('max_user_id', $max_user_id)
            ->first();

        return response()->json([
            'data' => [
                'max_user_id' => $max_user_id,
                'chat_id' => $subscriber?->chat_id,
                'scope' => $scope,
                'sport_ids' => $subscriber?->{$column} ?? [],
            ],
        ]);
    }

    public function upsert(Request $request): JsonResponse
    {
        $scope = $this->normalizeScope($request->input('scope'));
        $column = $this->columnByScope($scope);

        $validated = $request->validate([
            'max_user_id' => ['required', 'integer', 'min:1'],
            'chat_id' => ['nullable', 'integer'],
            'scope' => ['nullable', 'string'],
            // Allow empty array to support "clear subscriptions" action
            'sport_ids' => ['present', 'array'],
            'sport_ids.*' => ['integer', Rule::exists('sports', 'id')],
        ]);

        MaxBotSubscriber::query()->updateOrCreate(
            ['max_user_id' => $validated['max_user_id']],
            [
                'chat_id' => $validated['chat_id'] ?? null,
                $column => array_values(array_unique($validated['sport_ids'])),
            ],
        );

        return response()->json(['ok' => true]);
    }
}
