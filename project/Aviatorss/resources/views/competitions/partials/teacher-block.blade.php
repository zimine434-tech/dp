@php
    $teacher = $competition->teacher?->user;
@endphp

<div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-gray-800">Ответственный преподаватель</h2>
        <p class="text-sm text-gray-600">
            @if($teacher)
                {{ $teacher->lastname }} {{ $teacher->firstname }} {{ $teacher->patronymic ?? '' }}
            @else
                Не назначен
            @endif
        </p>
    </div>
    @if(auth()->user()?->role === 'teacher' && $competition->status === 'upcoming')
        <div class="flex items-center gap-2">
            <button
                type="button"
                class="px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition text-sm"
                onclick="document.getElementById('assign-teacher-form').classList.toggle('hidden')"
            >
                Изменить
            </button>
        </div>
    @endif
</div>

