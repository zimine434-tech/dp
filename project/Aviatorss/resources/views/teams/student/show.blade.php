@extends('layouts.student')

@section('title', $team->name)

@section('content')
    @php
        $teamBackFromProfile = request()->query('from') === 'profile';
        $teamBackUrl = $teamBackFromProfile ? route('profile.participations.teams') : route('teams.index');
        $teamBackLabel = $teamBackFromProfile ? 'Назад к командам в профиле' : 'Назад к командам';
        $teamBackBtnClass = $teamBackFromProfile
            ? 'inline-flex shrink-0 items-center justify-center rounded-lg border border-blue-300/80 bg-white px-4 py-2.5 text-sm font-semibold text-blue-900 shadow-sm transition hover:bg-blue-100/50 hover:border-blue-400 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-600 focus-visible:ring-offset-2 max-sm:py-2 max-sm:px-3 max-sm:text-xs text-center'
            : 'inline-flex items-center justify-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 sm:text-base';
    @endphp
    <div class="mx-auto max-w-5xl space-y-6 sm:space-y-8">
        <!-- Шапка -->
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0 flex-1">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 break-words sm:text-4xl">
                        {{ $team->name }}
                    </h1>
                    @if(filled($team->description))
                        @include('partials.rich-text', ['html' => $team->description, 'class' => 'mt-2 max-w-3xl text-base leading-relaxed text-gray-700'])
                    @else
                        <p class="mt-2 max-w-3xl text-sm leading-relaxed text-gray-500">
                            Описание команды пока не заполнено.
                        </p>
                    @endif
                </div>
                <div class="flex shrink-0 sm:pt-1">
                    <a href="{{ $teamBackUrl }}" class="{{ $teamBackBtnClass }}">
                        {{ $teamBackLabel }}
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 lg:items-stretch">
            <!-- Статистика -->
            <section class="flex min-w-0">
                <div class="flex w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Статистика</h2>
                        <p class="mt-0.5 text-sm text-gray-500">Активные участники</p>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex min-h-[10rem] flex-1 flex-col justify-center rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50/80 p-6 ring-1 ring-blue-100/80">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-white shadow-sm ring-1 ring-blue-100">
                                    <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                                <div class="min-w-0">
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
                                    <p class="text-sm font-medium text-gray-600">{{ $text }}</p>
                                    <p class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">{{ $count }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Заявки -->
            <section class="flex min-w-0">
                <div class="flex w-full flex-col overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md">
                    <div class="border-b border-gray-100 px-6 py-4">
                        <h2 class="text-lg font-semibold text-gray-900">Заявка в команду</h2>
                        <p class="mt-0.5 text-sm text-gray-500">Сообщение тренеру указывать необязательно</p>
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <div class="flex-1 rounded-xl border border-gray-200 bg-gray-50/90 p-5">
                            @if(session('success'))
                                <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                                    {{ session('success') }}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @php
                                $isAlreadyMember = $team->members->whereNull('out')->where('user_id', auth()->id())->count() > 0;
                            @endphp

                            @if($isAlreadyMember)
                                <div class="rounded-lg border border-blue-100 bg-blue-50/80 px-4 py-4 text-center">
                                    <p class="text-sm font-medium text-blue-900">Вы уже в этой команде</p>
                                    <p class="mt-1 text-xs text-blue-800/80">Заявка не требуется</p>
                                </div>
                            @elseif(!empty($joinRequest))
                                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-4 text-center">
                                    <p class="text-sm font-medium text-amber-900">Заявка на рассмотрении</p>
                                </div>
                            @else
                                <form action="{{ route('teams.join-requests.store', $team) }}" method="POST" class="space-y-4">
                                    @csrf
                                    @if(request()->query('from') === 'profile')
                                        <input type="hidden" name="return_from" value="profile">
                                    @endif
                                    <div>
                                        <label for="join-message" class="mb-1.5 block text-xs font-medium text-gray-600">Сообщение тренеру</label>
                                        <textarea
                                            id="join-message"
                                            name="message"
                                            rows="4"
                                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                                            placeholder="Например, опыт, пожелания по роли…"
                                        >{{ old('message') }}</textarea>
                                    </div>
                                    <button
                                        type="submit"
                                        class="w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2"
                                    >
                                        Подать заявку
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        @php
            $currentMembers = $team->members->whereNull('out');
            $currentMemberUserIds = $currentMembers->pluck('user_id')->filter()->all();
            $formerMembers = $team->members
                ->whereNotNull('out')
                ->whereNotIn('user_id', $currentMemberUserIds)
                ->sortByDesc(fn ($m) => optional($m->out)->timestamp ?? 0)
                ->unique('user_id')
                ->values();
        @endphp

        <!-- Состав -->
        <section class="overflow-hidden rounded-xl border border-gray-100 bg-white shadow-md">
            <div class="border-b border-gray-100 bg-gray-50/90 px-6 py-4">
                <h2 class="text-lg font-semibold text-gray-900">Состав команды</h2>
                <p class="mt-0.5 text-sm text-gray-500">Текущие и бывшие участники</p>
            </div>
            <div class="border-b border-gray-100 px-4 py-3 sm:px-6">
                <nav class="flex flex-wrap gap-2" aria-label="Состав команды">
                    <button
                        type="button"
                        id="tab-roster-current"
                        class="roster-tab-button inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 border-blue-600 bg-blue-600 text-white shadow-sm"
                        onclick="switchRosterTab('current')"
                    >
                        Текущий состав ({{ $currentMembers->count() }})
                    </button>
                    <button
                        type="button"
                        id="tab-roster-former"
                        class="roster-tab-button inline-flex items-center justify-center rounded-full border px-4 py-2 text-sm font-medium transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 border-gray-200 bg-white text-gray-700 hover:bg-gray-50"
                        onclick="switchRosterTab('former')"
                    >
                        Бывшие участники ({{ $formerMembers->count() }})
                    </button>
                </nav>
            </div>

            <div id="content-roster-current" class="roster-tab-content p-6">
                @if($currentMembers->count() > 0)
                    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Имя</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:table-cell sm:px-6">Роль</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 md:table-cell md:px-6">В команде с</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($currentMembers as $member)
                                    <tr class="transition hover:bg-gray-50/80">
                                        <td class="px-4 py-4 sm:px-6">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $member->user->firstname }} {{ $member->user->lastname }}
                                            </div>
                                            <div class="mt-1 sm:hidden">
                                                @if($member->type_user === 'capitan')
                                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-800">Капитан</span>
                                                @elseif($member->type_user === 'member')
                                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-800">Участник</span>
                                                @else
                                                    <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-800">Не указано</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="hidden px-4 py-4 sm:table-cell sm:px-6">
                                            @if($member->type_user === 'capitan')
                                                <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">Капитан</span>
                                            @elseif($member->type_user === 'member')
                                                <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">Участник</span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">Не указано</span>
                                            @endif
                                        </td>
                                        <td class="hidden px-4 py-4 text-sm text-gray-500 md:table-cell md:px-6">
                                            {{ $member->joined_at->format('d.m.Y') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-12 text-center">
                        <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <p class="mt-3 text-sm font-medium text-gray-700">В команде пока нет участников</p>
                    </div>
                @endif
            </div>

            <div id="content-roster-former" class="roster-tab-content hidden p-6">
                @if($formerMembers->count() > 0)
                    <div class="overflow-x-auto rounded-lg ring-1 ring-gray-200">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 sm:px-6">Имя</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 md:table-cell md:px-6">Присоединился</th>
                                    <th class="hidden px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 md:table-cell md:px-6">Выбыл</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach($formerMembers as $member)
                                    <tr class="transition hover:bg-gray-50/80">
                                        <td class="px-4 py-4 sm:px-6">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $member->user->firstname }} {{ $member->user->lastname }}
                                            </div>
                                        </td>
                                        <td class="hidden px-4 py-4 text-sm text-gray-500 md:table-cell md:px-6">
                                            {{ $member->joined_at?->format('d.m.Y') ?? '—' }}
                                        </td>
                                        <td class="hidden px-4 py-4 text-sm text-gray-500 md:table-cell md:px-6">
                                            {{ $member->out?->format('d.m.Y') ?? '—' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-xl border border-dashed border-gray-200 bg-gray-50/80 px-6 py-12 text-center">
                        <p class="text-sm font-medium text-gray-700">Бывших участников нет</p>
                    </div>
                @endif
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script>
function switchRosterTab(tabName) {
    document.querySelectorAll('.roster-tab-content').forEach(function (el) {
        el.classList.add('hidden');
    });
    document.querySelectorAll('.roster-tab-button').forEach(function (btn) {
        btn.classList.remove('border-blue-600', 'bg-blue-600', 'text-white', 'shadow-sm');
        btn.classList.add('border-gray-200', 'bg-white', 'text-gray-700', 'hover:bg-gray-50');
    });

    const content = document.getElementById('content-roster-' + tabName);
    const button = document.getElementById('tab-roster-' + tabName);
    if (content) {
        content.classList.remove('hidden');
    }
    if (button) {
        button.classList.add('border-blue-600', 'bg-blue-600', 'text-white', 'shadow-sm');
        button.classList.remove('border-gray-200', 'bg-white', 'text-gray-700', 'hover:bg-gray-50');
    }
}
</script>
@endpush
