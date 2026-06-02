@php
    $user = $participant->user;
    $userId = (int) ($user?->id ?? 0);
    $form = $form ?? null;
    $formsLocked = $formsLocked ?? false;
    $formsReturnOnly = $formsReturnOnly ?? false;
    $formIssuedDefault = $form && ($form->form_issued || $form->form_type_id) ? '1' : '0';
    $formIssued = (string) old('forms.'.$userId.'.form_issued', $formIssuedDefault);
    $formIssuedYes = $formIssued === '1';
    $selectedFormTypeName = '';
    if ($form?->form_type_id) {
        $selectedFormTypeName = $form->formType?->name ?? $form->form_view ?? '';
    }
    $formTypeId = (string) old('forms.'.$userId.'.form_type_id', (string) ($form?->form_type_id ?? ''));
    $formStatus = (string) old('forms.'.$userId.'.form_status', (string) ($form?->form_status ?? 'pending'));
    $formNumber = old('forms.'.$userId.'.form_number', $form?->form_number ?? '');
    $issueFieldsLocked = $formsLocked || $formsReturnOnly;
    $issuedOptions = [
        ['value' => '0', 'label' => 'Нет'],
        ['value' => '1', 'label' => 'Да'],
    ];
    $statusOptions = [
        ['value' => 'pending', 'label' => 'Не сдал'],
        ['value' => 'submitted', 'label' => 'Сдал'],
    ];
    $formTypeOptions = collect($competitionFormTypes ?? [])->map(fn ($t) => [
        'value' => (string) $t->id,
        'label' => (string) $t->name,
    ])->values()->all();
    $statusLabel = $formStatus === 'submitted' ? 'Сдал' : 'Не сдал';
@endphp
<tr
    class="hover:bg-gray-50 competition-form-row"
    data-user-id="{{ $userId }}"
    data-form-issued="{{ $formIssuedYes ? '1' : '0' }}"
    @if($formsReturnOnly) data-form-return-only="1" @endif
>
    <td class="px-2 sm:px-3 py-3 align-top">
        <div class="text-sm font-medium text-gray-900 break-words">{{ $user->lastname }} {{ $user->firstname }}</div>
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($issueFieldsLocked)
            <p class="text-sm text-gray-900">{{ $formIssuedYes ? 'Да' : 'Нет' }}</p>
        @else
            <x-filter-combobox
                name="forms[{{ $userId }}][form_issued]"
                :selected="$formIssued"
                :options="$issuedOptions"
                input-id="comp_form_issued_{{ $userId }}"
                input-class="competition-form-issued"
                variant="default"
            />
        @endif
        <p class="competition-form-issued-date mt-1 text-xs text-gray-500 break-words {{ $formIssuedYes && $form?->issued_at ? '' : 'hidden' }}">
            @if($form?->issued_at)
                Дата выдачи: {{ $form->formattedIssuedAt() }}
            @endif
        </p>
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($issueFieldsLocked)
            <p class="text-sm text-gray-900 break-words">{{ $formIssuedYes && $selectedFormTypeName !== '' ? $selectedFormTypeName : '—' }}</p>
        @else
            <div class="competition-form-type-wrap {{ $formIssuedYes ? '' : 'hidden' }}">
                <x-filter-combobox
                    name="forms[{{ $userId }}][form_type_id]"
                    :selected="$formTypeId"
                    :options="$formTypeOptions"
                    empty-label="—"
                    input-id="comp_form_type_{{ $userId }}"
                    input-class="competition-form-type"
                    variant="default"
                />
            </div>
            <p class="competition-form-type-fallback text-sm text-gray-500 {{ $formIssuedYes ? 'hidden' : '' }}">—</p>
        @endif
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($issueFieldsLocked)
            <p class="text-sm text-gray-900">{{ $formIssuedYes && filled($formNumber) ? $formNumber : '—' }}</p>
        @else
            <input
                type="text"
                name="forms[{{ $userId }}][form_number]"
                class="competition-form-number w-full rounded-lg border border-gray-300 bg-white px-2 py-2 text-sm text-gray-900 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 disabled:bg-gray-100 disabled:text-gray-500"
                value="{{ $formNumber }}"
                placeholder="Номер"
                @disabled(! $formIssuedYes)
            >
        @endif
    </td>
    <td class="px-2 sm:px-3 py-3 align-top">
        @if($formsLocked)
            <p class="text-sm text-gray-900">{{ $formIssuedYes ? $statusLabel : '—' }}</p>
        @elseif($formsReturnOnly)
            @if($formIssuedYes)
                <x-filter-combobox
                    name="forms[{{ $userId }}][form_status]"
                    :selected="$formStatus"
                    :options="$statusOptions"
                    input-id="comp_form_status_{{ $userId }}"
                    input-class="competition-form-status"
                    variant="default"
                />
            @else
                <p class="text-sm text-gray-500">—</p>
            @endif
        @else
            <div class="competition-form-status-wrap {{ $formIssuedYes ? '' : 'hidden' }}">
                <x-filter-combobox
                    name="forms[{{ $userId }}][form_status]"
                    :selected="$formStatus"
                    :options="$statusOptions"
                    input-id="comp_form_status_{{ $userId }}"
                    input-class="competition-form-status"
                    variant="default"
                />
            </div>
            <p class="competition-form-status-fallback text-sm text-gray-500 {{ $formIssuedYes ? 'hidden' : '' }}">—</p>
        @endif
        <p class="competition-form-submitted-date mt-1 text-xs text-gray-500 break-words {{ $formIssuedYes && $formStatus === 'submitted' && $form?->submitted_at ? '' : 'hidden' }}">
            @if($form?->submitted_at)
                Дата сдачи: {{ $form->formattedSubmittedAt() }}
            @endif
        </p>
    </td>
</tr>
