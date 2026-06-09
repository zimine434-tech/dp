@extends('layouts.guest')

@section('title', $competition->name)

@section('content')
    @php
        $isPersonalCompetition = $competition->isPersonalCompetition();
    @endphp
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $competition->name }}</h1>
                </div>
                <a 
                    href="{{ route('guest.competitions') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Название</label>
                    <p class="text-lg text-gray-900">{{ $competition->name }}</p>
                </div>

                @if(! $isPersonalCompetition)
                    <div>
                        <label class="text-sm font-medium text-gray-500 block mb-1">Вид спорта</label>
                        <p class="text-lg text-gray-900">{{ $competition->sport?->name ?? '—' }}</p>
                    </div>
                @endif

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
                    @if($competition->location && $competition->location->organizer)
                        <p class="text-sm text-gray-500">Организатор: {{ $competition->location->organizer }}</p>
                    @endif
                </div>
            </div>

            @if($competition->description)
                <div class="mt-6 pt-6 border-t">
                    <label class="text-sm font-medium text-gray-500 block mb-2">Описание</label>
                    @include('partials.rich-text', ['html' => $competition->description, 'class' => 'text-gray-700'])
                </div>
            @endif
        </div>

        @php
            $competitionPhotos = $competition->images->sortBy('id')->values();
        @endphp
        @if($competitionPhotos->isNotEmpty())
            <div class="overflow-hidden rounded-lg bg-white shadow-md">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Фотографии</h2>
                    <p class="mt-1 text-sm text-gray-600">Снимки с соревнования (нажмите для просмотра в полном размере).</p>
                </div>
                <div class="flex h-[min(28rem,55vh)] min-h-[14rem] w-full flex-col overflow-hidden bg-gray-50">
                    @include('news.partials.news-images-carousel', [
                        'images' => $competitionPhotos,
                        'altTitle' => $competition->name,
                        'description' => \App\Support\RichTextPlain::fromHtml($competition->description),
                        'fillCover' => true,
                    ])
                </div>
            </div>
            @include('news.partials.news-lightbox')
        @endif

        <!-- Участники -->
        @if($competition->participants->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-6">
                @php
                    $count = $competition->participants->count();
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
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Участники ({{ $count }})</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Фамилия</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Имя</th>
                                @if($isPersonalCompetition)
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Вид спорта</th>
                                @endif
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Роль</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($competition->participants as $participant)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $participant->user->lastname ?? '—' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $participant->user->firstname ?? '—' }}
                                        </div>
                                    </td>
                                    @if($isPersonalCompetition)
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">
                                                {{ $participant->team?->sport?->name ?? '—' }}
                                            </div>
                                        </td>
                                    @endif
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $role = $participant->role ?? 'student';
                                            $roleNames = [
                                                'student' => 'Участник',
                                                'teacher' => 'Преподаватель'
                                            ];
                                            $roleName = $roleNames[$role] ?? 'Участник';
                                        @endphp
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $roleName }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Участники</h2>
                <p class="text-gray-500">Пока нет участников в этом соревновании.</p>
            </div>
        @endif
    </div>
@endsection

