@extends('layouts.teacher')

@section('title', 'Детали вида спорта')

@section('content')
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Заголовок -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $sport->name }}</h1>
                    <p class="text-gray-600 mt-1 text-sm sm:text-base">Детальная информация о виде спорта</p>
                </div>
                <div class="flex gap-2">
                    <a 
                        href="{{ route('sports.index') }}" 
                        class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                    >
                        Назад к списку
                    </a>
                </div>
            </div>
        </div>

        <!-- Основная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Основная информация</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Название</label>
                    <p class="text-lg text-gray-900">{{ $sport->name }}</p>
                </div>
                
                <div class="md:col-span-2">
                    <label class="text-sm font-medium text-gray-500 block mb-1">Команды с этим видом спорта</label>
                    @if($sport->teams->isEmpty())
                        <p class="text-lg text-gray-900">Пока нет команд</p>
                    @else
                        <ul class="mt-2 list-disc list-inside text-blue-700">
                            @foreach($sport->teams as $t)
                                <li>
                                    <a href="{{ route('teams.show', $t) }}" class="hover:underline">{{ $t->name }}</a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                <div>
                    <label class="text-sm font-medium text-gray-500 block mb-1">Дата создания</label>
                    <p class="text-lg text-gray-900">{{ $sport->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>

            @if($sport->description)
                <div class="mt-6 pt-6 border-t">
                    <label class="text-sm font-medium text-gray-500 block mb-2">Описание</label>
                    @include('partials.rich-text', ['html' => $sport->description, 'class' => 'text-gray-700'])
                </div>
            @endif
        </div>

        <!-- Действия -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Действия</h2>
            <div class="flex flex-wrap gap-3">
                <a 
                    href="{{ route('sports.edit', $sport) }}" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                >
                    Редактировать
                </a>
                <form 
                    action="{{ route('sports.destroy', $sport) }}" 
                    method="POST" 
                    class="inline"
                    onsubmit="return confirm('Вы уверены, что хотите удалить этот вид спорта?')"
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
                    href="{{ route('sports.index') }}" 
                    class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm sm:text-base"
                >
                    Назад к списку
                </a>
            </div>
        </div>
    </div>
@endsection

