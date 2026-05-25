@props(['competition'])

<a
    href="{{ route('competitions.show', ['competition' => $competition, 'from' => 'results', 'add_result' => 1]) }}"
    class="text-green-600 hover:text-green-900 px-3 py-1 rounded hover:bg-green-50 transition whitespace-nowrap"
>
    Добавить результат
</a>
