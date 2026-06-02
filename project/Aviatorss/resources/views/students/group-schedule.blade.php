@extends('layouts.teacher')

@section('title', 'Расписание: '.$groupName)

@section('content')
    @php
        $scheduleNotFound = $scheduleNotFound ?? false;
        $headers = $schedule['headers'] ?? [];
        $times = $schedule['times'] ?? [];
        $rows = $schedule['rows'] ?? [];
        $hr = $highlight['row'] ?? null;
        $hc = $highlight['col'] ?? null;
    @endphp
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Расписание группы {{ $groupName }}</h1>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                @if($groups->isNotEmpty())
                    <form method="get" class="flex flex-wrap items-end gap-2" id="grp-switch">
                        <div>
                            <label for="grp_sel" class="block text-xs font-medium text-gray-500 mb-1">Группа</label>
                            <select id="grp_sel" name="group_override" class="rounded-lg border border-gray-300 px-3 py-2 text-sm min-w-[10rem]" onchange="const v=this.value; if(v) window.location='{{ url('/students/groups') }}/'+encodeURIComponent(v)+'/schedule?date={{ $date }}';">
                                <option value="">—</option>
                                @foreach($groups as $g)
                                    <option value="{{ $g->name }}" @selected($g->name === $groupName)>{{ $g->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                @endif
                <a href="{{ route('students.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm text-gray-700 hover:bg-gray-50">К студентам</a>
            </div>
        </div>

        @if($scheduleNotFound)
            <div class="rounded-lg border border-red-200 bg-red-50 px-6 py-8 text-center">
                <p class="text-lg font-semibold text-red-800">Расписание не найдено</p>
                <p class="mt-2 text-sm text-red-700">
                    Для группы «{{ $groupName }}» нет данных расписания на выбранную дату или группа отсутствует в справочнике.
                </p>
            </div>
        @else
        <div class="bg-white rounded-lg shadow-md overflow-x-auto">
            <table class="min-w-full text-sm border-collapse">
                <thead>
                    <tr>
                        <th class="border border-gray-200 bg-gray-50 px-2 py-2 text-left text-xs font-medium text-gray-600 w-24">Время</th>
                        @foreach($headers as $i => $h)
                            <th class="border border-gray-200 bg-gray-50 px-2 py-2 text-center text-xs font-medium text-gray-700">{{ $h['label'] ?? '' }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($times as $ri => $timeLabel)
                        <tr>
                            <td class="border border-gray-200 bg-gray-50 px-2 py-2 text-xs font-medium text-gray-700 whitespace-nowrap">{{ $timeLabel }}</td>
                            @foreach($headers as $ci => $_h)
                                @php
                                    $cards = $rows[$ri][$ci] ?? [];
                                    $isHi = $hr !== null && $hc !== null && (int)$ri === (int)$hr && (int)$ci === (int)$hc;
                                @endphp
                                <td class="border border-gray-200 px-1 py-1 align-top {{ $isHi ? 'bg-amber-50 ring-2 ring-amber-300' : 'bg-white' }} min-w-[7rem]">
                                    @forelse($cards as $card)
                                        <div class="mb-1 rounded border border-gray-100 bg-slate-50/80 px-1 py-1 text-[11px] leading-tight">
                                            @if(!empty($card['type']))
                                                <div class="text-[10px] uppercase text-gray-500">{{ $card['type'] }}</div>
                                            @endif
                                            @if(!empty($card['title']))
                                                <div class="font-semibold text-gray-900 break-words">{{ $card['title'] }}</div>
                                            @endif
                                            @if(!empty($card['teacher']))
                                                <div class="text-gray-600">{{ $card['teacher'] }}</div>
                                            @endif
                                            @if(!empty($card['place']))
                                                <div class="text-gray-500">{{ $card['place'] }}</div>
                                            @endif
                                        </div>
                                    @empty
                                        <span class="text-gray-300">—</span>
                                    @endforelse
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
@endsection
