@php
    $user = $participant->user;
    $userId = (int) ($user?->id ?? 0);
    $form = $form ?? null;
    $formIssued = $form && ($form->form_issued || $form->form_type_id);
    $formStatus = old('forms.'.$userId.'.form_status', $form?->form_status ?? 'pending');
@endphp
<tr class="hover:bg-gray-50 competition-form-row" data-user-id="{{ $userId }}" data-form-return-only="1">
    <td class="px-2 sm:px-3 py-3 align-top">
        <div class="text-sm font-medium text-gray-900 break-words">{{ $user->lastname }} {{ $user->firstname }}</div>
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($formIssued)
            <select
                name="forms[{{ $userId }}][form_status]"
                class="competition-form-status w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500"
            >
                <option value="pending" @selected($formStatus === 'pending')>Не сдал</option>
                <option value="submitted" @selected($formStatus === 'submitted')>Сдал</option>
            </select>
            <p class="competition-form-submitted-date mt-1 text-xs text-gray-500 break-words {{ $formStatus === 'submitted' && $form?->submitted_at ? '' : 'hidden' }}">
                @if($form?->submitted_at)
                    Дата сдачи: {{ $form->formattedSubmittedAt() }}
                @endif
            </p>
        @else
            <span class="text-sm text-gray-500">Форма не выдавалась</span>
        @endif
    </td>
</tr>
