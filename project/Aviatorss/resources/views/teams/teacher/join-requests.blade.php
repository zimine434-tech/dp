@extends('layouts.teacher')

@section('title', 'Заявки в команду: '.$team->name)

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h1 class="text-2xl font-bold text-gray-900 break-words">Заявки на вступление</h1>
                <p class="text-sm text-gray-600 mt-1 break-words">Команда: {{ $team->name }}</p>
            </div>
            <a
                href="{{ route('teams.show', ['team' => $team]) }}"
                class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700"
            >
                Назад к команде
            </a>
        </div>

        @if(session('success'))
            <div class="rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded-lg bg-white shadow-md">
            <div class="border-b px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-800">Список заявок</h2>
            </div>

            @if($requests->count() === 0)
                <div class="p-6 text-sm text-gray-600">Заявок пока нет.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Студент</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Сообщение</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Статус</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($requests as $r)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $r->user?->lastname }} {{ $r->user?->firstname }} {{ $r->user?->patronymic }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ $r->created_at?->format('d.m.Y H:i') }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                        @if(filled($r->message))
                                            <div class="max-w-xl whitespace-pre-wrap">{{ $r->message }}</div>
                                        @else
                                            <span class="text-gray-400">—</span>
                                        @endif
                                        @if(filled($r->review_note))
                                            <div class="mt-2 text-xs text-gray-500 whitespace-pre-wrap">
                                                <span class="font-medium">Комментарий:</span> {{ $r->review_note }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badge = match($r->status) {
                                                'pending' => ['bg-yellow-100 text-yellow-800', 'Ожидает'],
                                                'approved' => ['bg-green-100 text-green-800', 'Принята'],
                                                'rejected' => ['bg-red-100 text-red-800', 'Отклонена'],
                                                'cancelled' => ['bg-gray-100 text-gray-800', 'Отменена'],
                                                default => ['bg-gray-100 text-gray-800', $r->status],
                                            };
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badge[0] }}">
                                            {{ $badge[1] }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        @if($r->status === 'pending')
                                            <div class="flex flex-col gap-2 sm:flex-row sm:justify-end">
                                                <form method="POST" action="{{ route('teams.join-requests.approve', [$team, $r]) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                                                        Принять
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('teams.join-requests.reject', [$team, $r]) }}">
                                                    @csrf
                                                    <button type="submit" class="rounded-lg bg-red-600 px-3 py-2 text-sm font-medium text-white hover:bg-red-700">
                                                        Отклонить
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-6 py-4">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

