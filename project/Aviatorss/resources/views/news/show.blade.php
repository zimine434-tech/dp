@extends('layouts.teacher')

@section('title', 'Детали новости')

@section('content')
    <div class="mx-auto max-w-4xl space-y-8">
        <article class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/[0.04]">
            <div class="px-6 py-8 sm:px-10 sm:py-10">
                <div class="flex flex-col gap-6">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between sm:gap-6 lg:gap-10">
                        <h1 class="min-w-0 flex-1 text-balance text-3xl font-bold leading-[1.12] tracking-tight text-gray-900 sm:pr-4 sm:text-4xl">
                            {{ $news->name }}
                        </h1>
                        <a
                            href="{{ route('news.index') }}"
                            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base sm:self-start"
                        >
                            Назад к списку
                        </a>
                    </div>

                    @if(filled($news->description))
                        <div class="max-w-[65ch] text-left text-[17px] leading-[1.75] text-gray-600 break-words">
                            @include('partials.rich-text', ['html' => $news->description])
                        </div>
                    @endif
                </div>
            </div>
        </article>

        <!-- Служебные поля и фото -->
        <div class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/[0.04]" style="max-width: 100%; overflow-x: hidden;">
            <div class="flex flex-col">
                <div class="min-w-0 border-b border-gray-100 px-6 py-8 sm:px-10 sm:pb-8 sm:pt-9">
                    <h2 class="mb-6 text-lg font-semibold tracking-tight text-gray-900">Основная информация</h2>

                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-500">Дата новости</label>
                            <p class="text-lg text-gray-900">{{ $news->date->format('d.m.Y') }}</p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-500">Статус</label>
                            <p class="text-lg">
                                @if($news->status === 'Published')
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-semibold text-green-800">
                                        Опубликовано
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold text-gray-800">
                                        Черновик
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-500">Создатель</label>
                            <p class="text-lg text-gray-900">
                                {{ $news->creator ? $news->creator->firstname . ' ' . $news->creator->lastname : 'Не указан' }}
                            </p>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-gray-500">Дата создания</label>
                            <p class="text-lg text-gray-900">{{ $news->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                    </div>
                </div>
                @include('news.partials.news-image-sidebar', ['news' => $news, 'showStoragePath' => true])
            </div>
        </div>

        <!-- Действия -->
        <div class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white p-6 shadow-sm ring-1 ring-gray-900/[0.04]" style="max-width: 100%; overflow-x: hidden;">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Действия</h2>
            <div class="flex flex-wrap gap-3">
                <a
                    href="{{ route('news.edit', $news) }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Редактировать
                </a>
                <form
                    action="{{ route('news.destroy', $news) }}"
                    method="POST"
                    class="inline"
                    onsubmit="return confirm('Вы уверены, что хотите удалить эту новость?')"
                >
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition"
                    >
                        Удалить
                    </button>
                </form>
                <a
                    href="{{ route('news.index') }}"
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>
    </div>
@endsection
