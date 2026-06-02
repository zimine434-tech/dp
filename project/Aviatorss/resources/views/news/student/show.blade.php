@extends('layouts.student')

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

                    <div class="mt-2 border-t border-gray-100 pt-6">
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-gray-500">
                            <span class="inline-flex items-center gap-2">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <time datetime="{{ $news->date->format('Y-m-d') }}">Дата публикации: {{ $news->date->format('d.m.Y') }}</time>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </article>

        <div class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm ring-1 ring-gray-900/[0.04]" style="max-width: 100%; overflow-x: hidden;">
            <div class="flex flex-col">
                @include('news.partials.news-image-sidebar', ['news' => $news])
            </div>
        </div>
    </div>
@endsection
