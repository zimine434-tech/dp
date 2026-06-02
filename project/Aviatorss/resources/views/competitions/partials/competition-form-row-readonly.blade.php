@php
    $user = $participant->user;
    $form = $form ?? null;
    $returned = $form && ($form->form_status ?? 'pending') === 'submitted';
@endphp
<tr class="hover:bg-gray-50">
    <td class="px-2 sm:px-3 py-3 align-top">
        <div class="text-sm font-medium text-gray-900 break-words">{{ $user->lastname }} {{ $user->firstname }}</div>
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($returned)
            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">Сдал</span>
            @if($form?->submitted_at)
                <p class="mt-1 text-xs text-gray-500">Дата сдачи: {{ $form->formattedSubmittedAt() }}</p>
            @endif
        @else
            <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-700">Не сдал</span>
        @endif
    </td>
</tr>
