@extends('layouts.staff')
@section('title', 'My Timetable')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>
<div class="mb-4 md:mb-8">
    <h1 class="text-2xl md:text-4xl font-bold text-gray-800 mb-2">My Timetable</h1>
</div>
<div class="flex items-center justify-between p-2 md:p-3 border border-gray-300 rounded-lg shadow-sm bg-white mb-3 md:mb-4">
    
    <div class="flex items-center">
        <button id="prevWeekBtn" class="flex items-center text-blue-700 font-semibold px-3 py-2 md:px-2 md:py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out touch-manipulation">
            <span class="mr-1 md:mr-2 font-bold text-gray-700 text-lg md:text-base">&lt;</span>
            <span class="md:hidden text-sm">Prev</span>
        </button>
    </div>

    <div id="weekRange" class="text-sm md:text-lg font-bold text-gray-800 mx-2 md:mx-4 text-center flex-1"></div>

    <div class="flex items-center">
        <button id="nextWeekBtn" class="flex items-center text-blue-700 font-semibold px-3 py-2 md:px-2 md:py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out touch-manipulation">
            <span class="md:hidden text-sm">Next</span>
            <span class="ml-1 md:ml-2 font-bold text-gray-700 text-lg md:text-base">&gt;</span>
        </button>
    </div>
</div>

<div class="bg-white rounded-lg shadow-lg overflow-x-auto -mx-2 md:mx-0 scrollbar-thin scrollbar-thumb-purple-300 scrollbar-track-gray-100" style="scrollbar-width: thin;">
    <div class="inline-block min-w-full align-middle">
    <table class="min-w-full text-[10px] sm:text-xs">
<!-- Table Header -->
        <thead class="bg-purple-50 sticky top-0 z-10">
            <tr>
                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[100px] md:min-w-[120px] w-[100px] md:w-[120px]">
                    Staff Name
                </th>
                <th class="px-2 md:px-6 py-2 md:py-3 text-left text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[90px] md:min-w-[130px] w-[90px] md:w-[130px]">
                    Department
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Mon
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Tue
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Wed
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Thu
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Fri
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Sat
                </th>
                <th class="px-1 md:px-6 py-2 md:py-3 text-center text-xs md:text-sm font-bold text-purple-800 uppercase tracking-wider whitespace-nowrap min-w-[80px] md:min-w-[110px] w-[80px] md:w-[110px]">
                    Sun
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($staff as $index => $staffMember)
            @php $isCurrent = (isset($staffMember->user) && isset($staffMember->user->id) && $staffMember->user->id === auth()->id()); @endphp
            <tr class="border-b {{ $isCurrent ? 'bg-blue-50' : '' }}">
                <td class="px-1 md:px-2 py-1 md:py-2 font-semibold {{ $isCurrent ? 'text-blue-600' : '' }} text-[10px] sm:text-xs">{{ $staffMember->user->name }}</td>
                <td class="px-1 md:px-2 py-1 md:py-2 text-center text-gray-600 text-[10px] sm:text-xs">{{ $staffMember->department ?? '' }}</td>
                @foreach($dates as $day)
                    @php
                        $key = $staffMember->user->id . '|' . $day;
                        $shift = isset($shiftsByKey[$key]) ? $shiftsByKey[$key] : null;
                        $leaveStatus = ($shift && $shift->leave) ? $shift->leave->status : null;
                        $hasOvertime = ($shift && $shift->overtime && $shift->overtime->status === 'approved');
                        $otHours = $hasOvertime ? $shift->overtime->hours : null;
                        $isBeforeHireDate = $day < $staffMember->hire_date->format('Y-m-d');
                    @endphp
                    <td class="px-1 md:px-2 py-1 md:py-2 {{ $isBeforeHireDate ? 'bg-gray-300' : (($shift && $leaveStatus === 'approved') ? 'bg-red-100' : ($shift ? (isset($shift->rest_day) && $shift->rest_day ? 'bg-yellow-100' : 'bg-green-100') : 'bg-gray-50')) }} text-[9px] sm:text-xs">
                        @if($isBeforeHireDate)
                            <span class="text-white font-semibold text-[9px] sm:text-xs">No Shift</span>
                        @elseif($shift && $leaveStatus === 'approved')
                            <span class="font-semibold text-red-600 text-[9px] sm:text-xs">LEAVE</span>
                        @elseif($shift)
                            @if(isset($shift->rest_day) && $shift->rest_day)
                                <span class="font-semibold text-red-600 text-[9px] sm:text-xs">REST</span>
                            @else
                                <span class="block">{{ $shift->start_time }} - {{ $shift->end_time }}</span>
                                <span class="text-[8px] sm:text-xs text-gray-500">B: {{ $shift->break_minutes ?? 0 }}m</span>
                                @if($hasOvertime && $otHours)
                                    <br><span class="text-[8px] sm:text-xs font-semibold text-purple-600">OT: {{ number_format($otHours, 1) }}h</span>
                                @endif
                            @endif
                        @else
                            <span class="text-gray-400 italic text-[9px] sm:text-xs">No Shift</span>
                        @endif
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
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
