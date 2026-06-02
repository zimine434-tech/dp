@php
    $user = $participant->user;
    $userId = (int) ($user?->id ?? 0);
    $admissionEditable = $admissionEditable ?? $competition->medicalAdmissionStatusEditable();
    $admissionStatus = (string) old(
        'admissions.'.$userId.'.medical_admission_status',
        $participant->medical_admission_status ?? 'pending'
    );
    $admissionOptions = [
        ['value' => 'pending', 'label' => 'Не отмечено'],
        ['value' => 'admitted', 'label' => 'Допущен'],
        ['value' => 'not_admitted', 'label' => 'Не допущен'],
    ];
    $admissionLabel = match ($admissionStatus) {
        'admitted' => 'Допущен',
        'not_admitted' => 'Не допущен',
        default => 'Не отмечено',
    };
@endphp
<tr class="hover:bg-gray-50 competition-admission-row" data-user-id="{{ $userId }}">
    <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
        <div class="text-sm font-medium text-gray-900">{{ $user->lastname }}</div>
    </td>
    <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
        <div class="text-sm text-gray-900">{{ $user->firstname }}</div>
    </td>
    <td class="px-3 sm:px-6 py-3 whitespace-nowrap">
        @if($admissionEditable)
            <x-filter-combobox
                name="admissions[{{ $userId }}][medical_admission_status]"
                :selected="$admissionStatus"
                :options="$admissionOptions"
                input-id="comp_admission_{{ $userId }}"
                input-class="competition-admission-status"
                variant="default"
            />
        @else
            <p class="text-sm text-gray-900">{{ $admissionLabel }}</p>
        @endif
    </td>
</tr>
