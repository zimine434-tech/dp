@extends('layouts.student')

@section('title', 'Детали соревнования')

@section('content')
    @php
        $canApply = $competition->status === 'upcoming' && ! $isParticipant && empty($pendingApplication);
        $count = $competition->participants->count();
        if ($count > 0) {
            $lastDigit = $count % 10;
            $lastTwoDigits = $count % 100;
            if (($lastTwoDigits >= 11 && $lastTwoDigits <= 14) || $lastDigit === 0 || $lastDigit >= 5) {
                $participantLabel = 'Участников';
            } elseif ($lastDigit === 1) {
                $participantLabel = 'Участник';
            } else {
                $participantLabel = 'Участника';
            }
        }
    @endphp
    <div class="mx-auto w-full max-w-4xl space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $competition->name }}</h1>
                    @if(filled($competition->description))
                        @include('partials.rich-text', ['html' => $competition->description, 'class' => 'mt-2 text-sm sm:text-base text-gray-600'])
                    @endif
                </div>
                <a 
                    href="{{ $competitionShowBack['url'] ?? route('competitions.index') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    {{ $competitionShowBack['label'] ?? 'Назад к списку' }}
                </a>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(($competition->result_type ?? 'team') !== 'personal')
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                        <p class="text-lg text-gray-900">{{ $competition->sport?->name ?? '—' }}</p>
                    </div>
                @endif

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Категория</label>
                    <p class="text-lg text-gray-900">{{ $competition->category?->name_category ?? 'Не указана' }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Вид участия</label>
                    <p class="text-lg text-gray-900">{{ $competition->resultFormatLabel() }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата начала</label>
                    <p class="text-lg text-gray-900">{{ $competition->start_date->format('d.m.Y') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата окончания</label>
                    <p class="text-lg text-gray-900">{{ $competition->end_date->format('d.m.Y') }}</p>
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Локация</label>
                    <p class="text-lg text-gray-900">{{ $competition->location->location ?? 'Не указана' }}</p>
                    @if($competition->location && filled($competition->location->address))
                        <p class="text-sm text-gray-500 mt-1">Адрес: {{ $competition->location->address }}</p>
                    @endif
                    @if($competition->location && $competition->location->organizer)
                        <p class="text-sm text-gray-500">Организатор: {{ $competition->location->organizer }}</p>
                    @endif
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Статус</label>
                    <div class="mt-1">
                        @if($competition->status === 'upcoming')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                Предстоящее
                            </span>
                        @elseif($competition->status === 'ongoing')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                Идет
                            </span>
                        @elseif($competition->status === 'finished')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                Завершено
                            </span>
                        @elseif($competition->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                Отменено
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Моя заявка -->
        <div id="my-application" class="bg-white rounded-lg shadow-md p-6 scroll-mt-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-gray-800">Моя заявка</h2>
                    @if($competition->status !== 'upcoming')
                        <p class="mt-1 text-sm text-gray-600">
                            @if($competition->status === 'ongoing')
                                Соревнование уже идёт — заявки не принимаются.
                            @elseif($competition->status === 'finished')
                                Соревнование завершено — заявки не принимаются.
                            @elseif($competition->status === 'cancelled')
                                Соревнование отменено.
                            @endif
                        </p>
                    @endif
                </div>

                @if($canApply)
                    <form action="{{ route('competitions.apply', $competition) }}" method="POST" class="shrink-0">
                        @csrf
                        <button
                            type="submit"
                            class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 sm:w-auto"
                        >
                            Подать заявку на участие
                        </button>
                    </form>
                @endif
            </div>

            <div class="mt-4 space-y-3">
                @if(session('success'))
                    <div class="rounded border-l-4 border-green-400 bg-green-50 p-4">
                        <p class="text-sm text-green-700">{{ session('success') }}</p>
                    </div>
                @endif

                @if(session('error'))
                    <div class="rounded border-l-4 border-red-400 bg-red-50 p-4">
                        <p class="text-sm text-red-700">{{ session('error') }}</p>
                    </div>
                @endif

                @if($isParticipant)
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
                        Вы в списке участников этого соревнования.
                    </div>
                @elseif(!empty($latestAcceptedApplication) && $competition->status === 'upcoming')
                    <div class="rounded-md border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-900">
                        Заявка принята.
                        @if($latestAcceptedApplication->accepted_at)
                            <span class="mt-1 block text-xs text-green-800/90">Дата принятия: {{ $latestAcceptedApplication->accepted_at->format('d.m.Y H:i') }}</span>
                        @endif
                    </div>
                @elseif($pendingApplication && $competition->status === 'upcoming')
                    <div class="rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                        Заявка отправлена и ожидает рассмотрения преподавателем.
                        <span class="mt-1 block text-xs text-amber-800/90">Дата подачи: {{ $pendingApplication->created_at->format('d.m.Y H:i') }}</span>
                    </div>
                @elseif(!empty($latestExpiredApplication))
                    <div class="rounded-md border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-800">
                        <p>Заявка не была рассмотрена.</p>
                        @if(filled($latestExpiredApplication->rejection_reason))
                            <p class="mt-1 text-gray-600">{{ $latestExpiredApplication->rejection_reason }}</p>
                        @endif
                        @if($latestExpiredApplication->created_at)
                            <span class="mt-1 block text-xs text-gray-500">Дата подачи: {{ $latestExpiredApplication->created_at->format('d.m.Y H:i') }}</span>
                        @endif
                    </div>
                @elseif($latestRejectedApplication && $competition->status === 'upcoming')
                    <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                        <p>Последняя заявка отклонена.</p>
                        @if(filled($latestRejectedApplication->rejection_reason))
                            <p class="mt-1 text-red-800/90">Причина: {{ $latestRejectedApplication->rejection_reason }}</p>
                        @endif
                        @if($canApply)
                            <p class="mt-2 text-xs text-red-800/80">Вы можете подать новую заявку кнопкой справа.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        @if($isParticipant)
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Моя форма</h2>

                @if(filled($competition->form_regulation_text))
                    <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 p-4">
                        <h3 class="text-sm font-medium text-gray-700 mb-2">Требования к форме</h3>
                        @include('partials.rich-text', ['html' => $competition->form_regulation_text, 'class' => 'text-sm text-gray-700'])
                    </div>
                @endif

                @if($myCompetitionForm)
                    <div class="mb-4">
                        <label class="text-sm font-medium text-gray-500 block mb-1">Форма выдана</label>
                        <p class="text-lg text-gray-900">{{ $myCompetitionForm->issuedLabel() }}</p>
                        @if($myCompetitionForm->isIssued() && $myCompetitionForm->issued_at)
                            <p class="mt-1 text-sm text-gray-500">Дата выдачи: {{ $myCompetitionForm->formattedIssuedAt() }}</p>
                        @endif
                    </div>

                    @if($myCompetitionForm->isIssued())
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="text-sm font-medium text-gray-500 block mb-1">Вид формы</label>
                                <p class="text-lg text-gray-900">{{ $myCompetitionForm->formType?->name ?? $myCompetitionForm->form_view ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 block mb-1">Номер формы</label>
                                <p class="text-lg text-gray-900">{{ $myCompetitionForm->form_number ?? '—' }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500 block mb-1">Сдал</label>
                                <div class="mt-1">
                                    @if($myCompetitionForm->isSubmitted())
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                            Сдал
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            Не сдал
                                        </span>
                                    @endif
                                </div>
                                @if($myCompetitionForm->isSubmitted() && $myCompetitionForm->submitted_at)
                                    <p class="mt-1 text-sm text-gray-500">Дата сдачи: {{ $myCompetitionForm->formattedSubmittedAt() }}</p>
                                @endif
                                <p class="mt-1 text-xs text-gray-500">Отметку выставляет преподаватель.</p>
                            </div>
                        </div>
                    @endif
                @else
                    <p class="text-sm text-gray-600">
                        Преподаватель ещё не отметил статус формы. Когда отметит выдачу, здесь появятся данные.
                    </p>
                @endif
            </div>
        @endif

        <!-- Участники -->
        <div class="bg-white rounded-lg shadow-md p-6">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-xl font-semibold text-gray-800">
                        @if($count > 0)
                            {{ $participantLabel }} ({{ $count }})
                        @else
                            Студенты
                        @endif
                    </h2>
                </div>

                @if($count > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($competition->participants as $participant)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $participant->user->lastname }}
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $participant->user->firstname }}
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Участник
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-gray-500">Пока нет студентов в этом соревновании.</p>
                @endif
        </div>

        <!-- Действия -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="mb-4 text-xl font-semibold text-gray-800">Действия</h2>
            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ $competitionShowBack['url'] ?? route('competitions.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    {{ $competitionShowBack['label'] ?? 'Назад к списку' }}
                </a>
            </div>
        </div>
    </div>
@endsection
