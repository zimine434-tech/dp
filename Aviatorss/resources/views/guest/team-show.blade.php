@extends('layouts.guest')

@section('title', $team->name)

@section('content')
    <div class="space-y-6">
        <!-- Заголовок -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex-1 min-w-0">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $team->name }}</h1>
                @if(filled($team->description))
                    @include('partials.rich-text', ['html' => $team->description, 'class' => 'mt-2 text-sm sm:text-base text-gray-700 leading-relaxed'])
                @else
                    <p class="mt-2 text-sm sm:text-base text-gray-400 italic">Описание отсутствует</p>
                @endif
                @if($team->sport)
                    <p class="mt-2 text-sm sm:text-base text-gray-600">
                        <span class="text-gray-500">Вид спорта:</span>
                        <span class="font-medium text-gray-800">{{ $team->sport->name }}</span>
                    </p>
                @endif
            </div>
            <div class="flex items-center">
                <a 
                    href="{{ route('guest.teams') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-center text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:items-stretch">
            <!-- Статистика -->
            <div class="flex min-h-0 max-w-2xl">
                <div class="flex w-full flex-1 flex-col rounded-lg bg-white p-6 shadow-md">
                    <h2 class="mb-4 text-xl font-semibold text-gray-800">Статистика</h2>
                    <div class="flex flex-1 flex-col space-y-4">
                        <div class="flex flex-1 flex-col rounded-lg bg-blue-50 p-4">
                            <div class="flex flex-1 items-center">
                                <svg class="mr-3 h-8 w-8 shrink-0 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <div>
                                    @php
                                        $count = $team->members->whereNull('out')->count();
                                        $lastDigit = $count % 10;
                                        $lastTwoDigits = $count % 100;
                                        
                                        if ($count === 0 || ($lastTwoDigits >= 5 && $lastTwoDigits <= 20) || $lastDigit >= 5 || $lastDigit === 0) {
                                            $text = 'Участников';
                                        } elseif ($lastDigit === 1) {
                                            $text = 'Участник';
                                        } else {
                                            $text = 'Участника';
                                        }
                                    @endphp
                                    <p class="text-sm text-gray-600">{{ $text }}</p>
                                    <p class="text-2xl font-bold text-gray-900">{{ $count }}</p>
                                </div>
                            </div>
                        </div>

                        @auth
                            @if(auth()->user()?->role === 'student')
                                <div class="rounded-lg border border-gray-200 bg-gray-50 p-4">
                                    @if(session('success'))
                                        <div class="mb-3 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
                                            {{ session('success') }}
                                        </div>
                                    @endif
                                    @if(session('error'))
                                        <div class="mb-3 rounded border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                                            {{ session('error') }}
                                        </div>
                                    @endif

                                    <p class="mb-3 text-sm font-medium text-gray-800">Заявка на вступление</p>

                                    @php
                                        $isAlreadyMember = $team->members
                                            ->whereNull('out')
                                            ->where('user_id', auth()->id())
                                            ->count() > 0;
                                    @endphp

                                    @if($isAlreadyMember)
                                        <p class="text-sm text-gray-600">Вы уже состоите в этой команде.</p>
                                    @elseif(!empty($joinRequest))
                                        <p class="text-sm text-gray-600">Ваша заявка отправлена и ожидает рассмотрения.</p>
                                    @else
                                        <form action="{{ route('guest.teams.join-requests.store', $team) }}" method="POST" class="space-y-3">
                                            @csrf
                                            <textarea
                                                name="message"
                                                rows="3"
                                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                                                placeholder="Сообщение тренеру (необязательно)"
                                            >{{ old('message') }}</textarea>
                                            <button
                                                type="submit"
                                                class="w-full rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700"
                                            >
                                                Подать заявку
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
        </div>

        @php
            $currentMembers = $team->members->whereNull('out');
            $currentMemberUserIds = $currentMembers->pluck('user_id')->filter()->all();
            // В истории может быть несколько записей на одного пользователя (вступал/выбывал несколько раз).
            // Показываем только последнюю (по out).
            $formerMembers = $team->members
                ->whereNotNull('out')
                ->whereNotIn('user_id', $currentMemberUserIds)
                ->sortByDesc(fn ($m) => optional($m->out)->timestamp ?? 0)
                ->unique('user_id')
                ->values();
        @endphp

        <!-- Участники команды -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="border-b border-gray-200 px-6">
                <nav class="flex -mb-px" aria-label="Состав команды">
                    <button
                        type="button"
                        id="tab-roster-current"
                        class="roster-tab-button active px-6 py-4 text-sm font-medium text-blue-600 border-b-2 border-blue-600"
                        onclick="switchRosterTab('current')"
                    >
                        Текущий состав ({{ $currentMembers->count() }})
                    </button>
                    <button
                        type="button"
                        id="tab-roster-former"
                        class="roster-tab-button px-6 py-4 text-sm font-medium text-gray-500 hover:text-gray-700 border-b-2 border-transparent hover:border-gray-300"
                        onclick="switchRosterTab('former')"
                    >
                        Бывшие участники ({{ $formerMembers->count() }})
                    </button>
                </nav>
            </div>

            <!-- Текущий состав -->
            <div id="content-roster-current" class="roster-tab-content">
                <div class="p-6 pb-0">
                    <p class="text-sm text-gray-500 mb-4">Участники, которые сейчас в команде ({{ $currentMembers->count() }})</p>
                </div>

                @if($currentMembers->count() > 0)
                    <div class="p-6 pt-0">
                        <div class="overflow-x-auto -mx-6 sm:mx-0">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя и фамилия</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Роль в команде</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата присоединения</th>
                                                <th class="px-3 sm:px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Другое</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($currentMembers as $member)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $member->user->firstname }} {{ $member->user->lastname }}
                                                        </div>
                                                        <div class="mt-1 sm:hidden">
                                                            @if($member->type_user === 'capitan')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">Капитан</span>
                                                            @elseif($member->type_user === 'coach')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">Тренер</span>
                                                            @elseif($member->type_user === 'member')
                                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">Участник</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 hidden sm:table-cell">
                                                        @if($member->type_user === 'capitan')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Капитан</span>
                                                        @elseif($member->type_user === 'coach')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Тренер</span>
                                                        @elseif($member->type_user === 'member')
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Участник</span>
                                                        @else
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Не указано</span>
                                                        @endif
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->joined_at->format('d.m.Y') }}
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-right whitespace-nowrap">
                                                        @if($member->user)
                                                            <a href="{{ route('guest.users.show', $member->user) }}" class="text-sm font-medium text-blue-600 hover:text-blue-800">
                                                                Смотреть профиль
                                                            </a>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-6">
                        <p class="text-gray-500 text-center py-6">В команде пока нет участников</p>
                    </div>
                @endif
            </div>

            <!-- Бывшие участники -->
            <div id="content-roster-former" class="roster-tab-content hidden">
                <div class="p-6 pb-0">
                    <p class="text-sm text-gray-500 mb-4">Те, кто был в команде раньше ({{ $formerMembers->count() }})</p>
                </div>

                @if($formerMembers->count() > 0)
                    <div class="p-6 pt-0">
                        <div class="overflow-x-auto -mx-6 sm:mx-0">
                            <div class="inline-block min-w-full align-middle">
                                <div class="overflow-hidden sm:rounded-lg">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя и фамилия</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата присоединения</th>
                                                <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Дата выхода</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($formerMembers as $member)
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 sm:px-6 py-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            {{ $member->user->firstname }} {{ $member->user->lastname }}
                                                        </div>
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->joined_at?->format('d.m.Y') ?? '—' }}
                                                    </td>
                                                    <td class="px-3 sm:px-6 py-4 text-sm text-gray-500 hidden md:table-cell">
                                                        {{ $member->out?->format('d.m.Y') ?? '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-6">
                        <p class="text-gray-500 text-center py-6">Бывших участников нет</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function switchRosterTab(tabName) {
    document.querySelectorAll('.roster-tab-content').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('.roster-tab-button').forEach(btn => {
        btn.classList.remove('active', 'text-blue-600', 'border-blue-600');
        btn.classList.add('text-gray-500', 'border-transparent');
    });

    const content = document.getElementById('content-roster-' + tabName);
    const button = document.getElementById('tab-roster-' + tabName);
    if (content) content.classList.remove('hidden');
    if (button) {
        button.classList.add('active', 'text-blue-600', 'border-blue-600');
        button.classList.remove('text-gray-500', 'border-transparent');
    }
}
</script>
@endpush

