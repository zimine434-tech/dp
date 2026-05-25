@php
    $message = $message ?? '';
    $slots = max(1, (int) ($slots ?? 4));
@endphp
<div class="grid w-full grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-3 md:grid-cols-4">
    @foreach (range(1, $slots) as $slot)
        <div
            @if($slot !== 1) aria-hidden="true" @endif
            class="{{ $slot === 1
                ? 'flex min-h-[9rem] flex-col items-center justify-center rounded-xl border border-gray-100 bg-white p-6 text-center shadow-sm'
                : 'flex min-h-[9rem] flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/90 p-6 shadow-sm' }}"
        >
            @if($slot === 1)
                <p class="text-sm leading-snug text-gray-600">{{ $message }}</p>
            @endif
        </div>
    @endforeach
</div>
