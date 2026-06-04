@if($competition->status === 'upcoming')
    <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
        Предстоящее
    </span>
@elseif($competition->status === 'ongoing')
    <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800">
        Идет
    </span>
@elseif($competition->status === 'finished')
    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
        Завершено
    </span>
@elseif($competition->status === 'cancelled')
    <span class="inline-flex items-center rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-medium text-gray-700">
        Отменено
    </span>
@endif
