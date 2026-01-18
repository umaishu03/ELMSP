@extends('layouts.staff')
@section('title', 'My Timetable')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">My Timetable</h1>
</div>
<div class="flex items-center justify-between p-2 border border-gray-300 rounded-lg shadow-sm bg-white mb-4">
    
    <div class="flex items-center">
        <button id="prevWeekBtn" class="flex items-center text-blue-700 font-semibold px-2 py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">
            <span class="mr-2 font-bold text-gray-700">&lt;</span>
        </button>
    </div>

    <div id="weekRange" class="text-lg font-bold text-gray-800 mx-4"></div>

    <div class="flex items-center">
        <button id="nextWeekBtn" class="flex items-center text-blue-700 font-semibold px-2 py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">
            <span class="ml-2 font-bold text-gray-700">&gt;</span>
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg overflow-x-auto">
    <table class="min-w-full text-xs">
<!-- Table Header -->
        <thead class="bg-purple-50 sticky top-0 z-10">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[120px] w-[120px]">
                    Staff Name
                </th>
                <th class="px-6 py-3 text-left text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[130px] w-[130px]">
                    Department
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Mon
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Tue
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Wed
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Thu
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Fri
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Sat
                </th>
                <th class="px-6 py-3 text-center text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[110px] w-[110px]">
                    Sun
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $index => $staffMember)
            @php $isCurrent = (isset($staffMember->user) && isset($staffMember->user->id) && $staffMember->user->id === auth()->id()); @endphp
            <tr class="border-b {{ $isCurrent ? 'bg-blue-50' : '' }}">
                <td class="px-2 py-2 font-semibold {{ $isCurrent ? 'text-blue-600' : '' }}">{{ $staffMember->user->name }}</td>
                <td class="px-2 py-2 text-center text-gray-600">{{ $staffMember->department ?? '' }}</td>
                @foreach($dates as $day)
                    @php
                        $key = $staffMember->user->id . '|' . $day;
                        $shift = isset($shiftsByKey[$key]) ? $shiftsByKey[$key] : null;
                        $leaveStatus = ($shift && $shift->leave) ? $shift->leave->status : null;
                        $hasOvertime = ($shift && $shift->overtime && $shift->overtime->status === 'approved');
                        $otHours = $hasOvertime ? $shift->overtime->hours : null;
                        $isBeforeHireDate = $day < $staffMember->hire_date->format('Y-m-d');
                    @endphp
                    <td class="px-2 py-2 {{ $isBeforeHireDate ? 'bg-gray-300' : (($shift && $leaveStatus === 'approved') ? 'bg-red-100' : ($shift ? (isset($shift->rest_day) && $shift->rest_day ? 'bg-yellow-100' : 'bg-green-100') : 'bg-gray-50')) }}">
                        @if($isBeforeHireDate)
                            <span class="text-white font-semibold">No Shift</span>
                        @elseif($shift && $leaveStatus === 'approved')
                            <span class="font-semibold text-red-600">LEAVE</span>
                        @elseif($shift)
                            @if(isset($shift->rest_day) && $shift->rest_day)
                                <span class="font-semibold text-red-600">REST DAY</span>
                            @else
                                {{ $shift->start_time }} - {{ $shift->end_time }}<br>
                                <span class="text-xs text-gray-500">Break: {{ $shift->break_minutes ?? 0 }} min</span>
                                @if($hasOvertime && $otHours)
                                    <br><span class="text-xs font-semibold text-purple-600">Overtime: {{ number_format($otHours, 1) }} hrs</span>
                                @endif
                            @endif
                        @else
                            <span class="text-gray-400 italic">No Shift</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentWeekStart;
    try {
        const serverStart = '{{ $startDate ?? '' }}';
        if (serverStart && serverStart.length === 10) {
            currentWeekStart = getMonday(new Date(serverStart + 'T00:00:00'));
        } else {
            currentWeekStart = getMonday(new Date());
        }
    } catch (e) {
        currentWeekStart = getMonday(new Date());
    }

    const weekRangeElement = document.getElementById('weekRange');
    const prevWeekBtn = document.getElementById('prevWeekBtn');
    const nextWeekBtn = document.getElementById('nextWeekBtn');

    function getMonday(date) {
        let d = new Date(date);
        let day = d.getDay();
        let diff = d.getDate() - day + (day === 0 ? -6 : 1);
        d.setDate(diff);
        d.setHours(0,0,0,0);
        return d;
    }

    function updateWeekHeader() {
        let weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(currentWeekStart.getDate() + 6);

        const monthOptions = { month: 'short' };
        const dayOptions = { day: 'numeric' };
        const yearOptions = { year: 'numeric' };

        const startMonth = currentWeekStart.toLocaleDateString('en-US', monthOptions).toUpperCase().replace('.', '');
        const startDay = currentWeekStart.toLocaleDateString('en-US', dayOptions);
        const endDay = weekEnd.toLocaleDateString('en-US', dayOptions);
        const endYear = weekEnd.toLocaleDateString('en-US', yearOptions);

        if (weekRangeElement) {
            weekRangeElement.textContent = `${startMonth} ${startDay} - ${endDay}, ${endYear}`;
        }
    }

    function changeWeek(direction) {
        currentWeekStart.setDate(currentWeekStart.getDate() + direction);
        function pad(n){ return n<10 ? '0'+n : n }
        const y = currentWeekStart.getFullYear();
        const m = pad(currentWeekStart.getMonth() + 1);
        const d = pad(currentWeekStart.getDate());
        const qs = '?week_start=' + `${y}-${m}-${d}`;
        window.location.href = window.location.pathname + qs;
    }

    updateWeekHeader();

    if (prevWeekBtn) prevWeekBtn.addEventListener('click', function(){ changeWeek(-7); });
    if (nextWeekBtn) nextWeekBtn.addEventListener('click', function(){ changeWeek(7); });
});
</script>
@endsection
