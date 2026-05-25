@php
    /** @var \App\Models\Competition $competition */
    $pendingApps = $competition->relationLoaded('competitionApplications')
        ? $competition->competitionApplications
        : collect();
@endphp

@if($competition->status === 'upcoming')
    <div class="rounded-lg bg-white p-4 shadow-md sm:p-5">
        <h2 class="text-lg font-semibold text-gray-800">Заявки на участие</h2>
        <p class="mb-4 text-xs text-gray-600">Принять — в список участников. Крестик — отклонить (появится поле причины).</p>

        @if($pendingApps->isEmpty())
            <div class="rounded-md border border-dashed border-gray-200 bg-gray-50 py-6 text-center">
                <p class="text-xs text-gray-500">Входящих заявок пока нет</p>
            </div>
        @else
            <div class="overflow-x-auto rounded-md border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-2 py-2 text-left font-medium uppercase tracking-wide text-gray-500 sm:px-3">Студент</th>
                            <th class="hidden px-2 py-2 text-left font-medium uppercase tracking-wide text-gray-500 sm:table-cell sm:px-3">Группа</th>
                            <th class="hidden px-2 py-2 text-left font-medium uppercase tracking-wide text-gray-500 md:table-cell sm:px-3">Подано</th>
                            <th class="w-28 px-2 py-2 text-right font-medium uppercase tracking-wide text-gray-500 sm:px-3 sm:w-32"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach($pendingApps as $application)
                            @php $student = $application->student; @endphp
                            <tr class="align-middle">
                                <td class="max-w-[10rem] px-2 py-2 sm:max-w-none sm:px-3 sm:py-2.5">
                                    @if($student)
                                        <span class="font-medium text-gray-900">{{ $student->lastname }} {{ $student->firstname }}</span>
                                        <span class="mt-0.5 block truncate text-[11px] text-gray-500 sm:text-xs">{{ $student->login }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="hidden px-2 py-2 text-gray-700 sm:table-cell sm:px-3 sm:py-2.5">
                                    {{ $student?->group_name ?? '—' }}
                                </td>
                                <td class="hidden whitespace-nowrap px-2 py-2 text-gray-600 md:table-cell sm:px-3 sm:py-2.5">
                                    {{ $application->created_at->format('d.m.y H:i') }}
                                </td>
                                <td class="px-2 py-2 sm:px-3 sm:py-2.5">
                                    <div class="flex flex-col items-end gap-1">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form
                                                action="{{ route('competitions.applications.accept', [$competition, $application]) }}"
                                                method="POST"
                                                class="inline"
                                                title="Принять заявку"
                                            >
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-green-600 text-white shadow-sm transition hover:bg-green-700"
                                                    aria-label="Принять заявку"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            <button
                                                type="button"
                                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm transition hover:border-red-300 hover:bg-red-50 hover:text-red-600"
                                                onclick="window.toggleCompetitionReject({{ (int) $application->id }})"
                                                aria-label="Отклонить заявку"
                                                aria-expanded="false"
                                                data-reject-toggle="{{ (int) $application->id }}"
                                            >
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        <div id="competition-reject-{{ $application->id }}" class="hidden w-full min-w-[11rem] rounded-md border border-gray-200 bg-gray-50 p-2 sm:min-w-[14rem]">
                                            <form action="{{ route('competitions.applications.reject', [$competition, $application]) }}" method="POST" class="space-y-2">
                                                @csrf
                                                <label class="block text-[11px] font-medium text-gray-600">Причина (необязательно)</label>
                                                <textarea
                                                    name="rejection_reason"
                                                    rows="2"
                                                    maxlength="2000"
                                                    class="w-full rounded border border-gray-300 px-2 py-1.5 text-xs focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400"
                                                    placeholder="Укажите причину отклонения"
                                                ></textarea>
                                                <button
                                                    type="submit"
                                                    class="w-full rounded-md bg-red-600 px-2 py-1.5 text-xs font-medium text-white hover:bg-red-700"
                                                >
                                                    Подтвердить отклонение
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endif

@once
    @push('scripts')
        <script>
            (function () {
                window.toggleCompetitionReject = function (id) {
                    var sel = 'competition-reject-' + id;
                    var panel = document.getElementById(sel);
                    if (!panel) return;
                    document.querySelectorAll('[id^="competition-reject-"]').forEach(function (p) {
                        if (p.id !== sel) {
                            p.classList.add('hidden');
                        }
                    });
                    panel.classList.toggle('hidden');
                    if (!panel.classList.contains('hidden')) {
                        var ta = panel.querySelector('textarea');
                        if (ta) {
                            ta.focus();
                        }
                    }
                    var expanded = !panel.classList.contains('hidden');
                    document.querySelectorAll('[data-reject-toggle="' + id + '"]').forEach(function (btn) {
                        btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                    });
                };
            })();
        </script>
    @endpush
@endonce
