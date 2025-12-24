@extends('layouts.admin')
@section('title', 'Staff Timetable')
@section('content')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>

<div class="flex flex-col lg:flex-row gap-8">
    <div class="flex-1">
        <div class="flex items-center justify-between p-2 border border-gray-300 rounded-lg shadow-sm bg-white mb-4">
            <div class="flex items-center">
                <button id="prevWeekBtn" class="flex items-center text-blue-700 font-semibold px-2 py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">
                    <span class="mr-2 font-bold text-gray-700">&lt;</span>
                </button>
            </div>
            
            <div id="weekRange" class="text-lg font-bold text-gray-800 mx-4">
                </div>
            
            <div class="flex items-center">
                <button id="nextWeekBtn" class="flex items-center text-blue-700 font-semibold px-2 py-1 rounded-md hover:bg-gray-100 transition duration-150 ease-in-out">
                    <span class="ml-2 font-bold text-gray-700">&gt;</span>
                </button>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-lg overflow-x-auto max-w-full">
            <table class="w-full table-fixed text-xs">
                <!-- Table Header -->
                <thead class="bg-blue-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            #
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Name
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Dep
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Mon
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Tue
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Wed
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Thu
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Fri
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Sat
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-blue-800 uppercase tracking-wider">
                            Sun
                        </th>
                    </tr>
                </thead>
                
                <tbody>
                    @php
                        // Order departments as requested: manager, supervisor, cashier, kitchen, barista, waiter, joki
                        $deptOrder = ['manager' => 0, 'supervisor' => 1, 'cashier' => 2, 'kitchen' => 3, 'barista' => 4, 'waiter' => 5, 'joki' => 6];
                        $orderedStaff = collect($staff)->sortBy(function($s) use ($deptOrder) {
                            $d = strtolower(trim($s->department ?? ''));
                            return $deptOrder[$d] ?? 999;
                        })->values();
                    @endphp
                    @foreach($orderedStaff as $index => $staffMember)
                    <tr class="border-b align-top">
                        <td class="px-2 py-1 text-center align-top">{{ $index + 1 }}</td>
                        <td class="px-2 py-1 font-semibold align-top truncate">{{ $staffMember->user->name }}</td>
                        <td class="px-2 py-1 text-center text-gray-600 align-top truncate">{{ $staffMember->department }}</td>
                        @foreach($dates as $day)
                            @php
                                // Use keyed lookup to avoid issues with types and date formats
                                $key = $staffMember->user->id . '|' . $day;
                                $shift = isset($shiftsByKey[$key]) ? $shiftsByKey[$key] : null;
                                $leaveStatus = ($shift && $shift->leave) ? $shift->leave->status : null;
                            @endphp
                            <td class="px-2 py-1 cursor-pointer {{ ($shift && $leaveStatus === 'approved') ? 'bg-red-100' : ($shift ? (isset($shift->rest_day) && $shift->rest_day ? 'bg-yellow-100' : 'bg-green-100') : 'bg-gray-50') }} break-words" 
                                   data-user-id="{{ $staffMember->user->id }}" 
                                   data-date="{{ $day }}"
                                   data-shift-id="{{ $shift->id ?? '' }}"
                                   data-start_time="{{ $shift->start_time ?? '' }}"
                                   data-end_time="{{ $shift->end_time ?? '' }}"
                                   data-break_minutes="{{ $shift->break_minutes ?? '' }}"
                                   data-department="{{ $shift->staff->department ?? '' }}"
                                   data-rest_day="{{ $shift->rest_day ?? 0 }}"
                                   onclick="openEditShiftModal(this)">
                                @if($shift && $leaveStatus === 'approved')
                                    <span class="font-semibold text-red-600">LEAVE</span>
                                @elseif($shift)
                                    @if(isset($shift->rest_day) && $shift->rest_day)
                                        <span class="font-semibold text-red-600">REST DAY</span>
                                    @else
                                        {{ $shift->start_time }} - {{ $shift->end_time }}<br>
                                        <span class="text-xs text-gray-500">Break: {{ $shift->break_minutes ?? 0 }} min</span>
                                    @endif
                                @else
                                    <span class="text-gray-400 italic">Add Shift</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="w-full lg:w-80 flex-shrink-0">
        <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
            <div class="relative w-full mb-2">
                <input id="assignSearch" type="text" 
                    placeholder="Search" 
                    class="w-full pl-3 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">Department:</label>
                <select id="assignDepartment" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">Department</option>
                    <option>Supervisor</option>
                    <option>Cashier</option>
                    <option>Barista</option>
                    <option>Kitchen</option>
                    <option>Joki</option>
                    <option>Waiter</option>
                </select>
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">Employee(s):</label>
                <select id="assignUsers" multiple class="w-full px-3 py-2 border rounded-lg">
                    @foreach($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">Start Time:</label>
                <input id="assignStartTime" type="time" class="w-full px-3 py-2 border rounded-lg" value=""> 
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">End Time:</label>
                <input id="assignEndTime" type="time" class="w-full px-3 py-2 border rounded-lg" value=""> 
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">Break (mins):</label>
                <input id="assignBreakMinutes" type="number" min="0" class="w-full px-3 py-2 border rounded-lg" value="60">
            </div>
            <div class="mb-2 flex items-center gap-2">
                <input id="assignRestDay" type="checkbox" class="h-4 w-4" />
                <label for="assignRestDay" class="text-sm font-semibold">Rest Day</label>
            </div>
            
            <div class="mb-2">
                <div class="flex gap-1" id="daySelector">
                    <!-- Use buttons so they are keyboard-focusable and interactive -->
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="mon">M</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="tue">T</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="wed">W</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="thu">T</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="fri">F</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="sat">S</button>
                    <button type="button" class="day-toggle px-2 py-1 bg-blue-100 rounded text-xs font-semibold" data-day="sun">S</button>
                </div>
            </div>
            <button id="assignShiftsBtn" class="w-full mb-2 bg-blue-600 text-white py-2 rounded">+ ASSIGN SHIFTS</button>
            <button id="assignEditBtn" class="w-full mb-2 bg-green-600 text-white py-2 rounded">✎ EDIT SHIFT</button>
            <input type="hidden" id="assignEditShiftId" value="">
            <div id="assignStatus" class="mt-2 text-sm text-gray-700"></div>
            <div id="toast" class="fixed top-6 right-6 bg-green-600 text-white px-4 py-2 rounded shadow hidden"></div>
            <style>
                .cell-loading { position:relative; }
                .cell-loading:after { content:''; position:absolute; right:6px; top:6px; width:10px; height:10px; border-radius:50%; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; animation:spin 0.8s linear infinite; }
                @keyframes spin { to { transform: rotate(360deg); } }
            </style>
        </div>
    </div>
</div>

<div id="editShiftModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">EDIT SHIFT</h2>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">Start Time:</label>
            <input id="editStartTime" type="time" placeholder="Start Time" class="w-full px-3 py-2 border rounded-lg" value="">
        </div>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">End Time:</label>
            <input id="editEndTime" type="time" placeholder="End Time" class="w-full px-3 py-2 border rounded-lg" value="">
        </div>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">Break (mins):</label>
            <input id="editBreakMinutes" type="number" placeholder="Break (mins)" min="0" class="w-full px-3 py-2 border rounded-lg" value="60">
        </div>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">Department:</label>
            <input id="editDepartment" type="text" class="w-full px-3 py-2 border rounded-lg" value="">
        </div>
        <input type="hidden" id="editUserId" value="">
        <input type="hidden" id="editDate" value="">
            <div class="flex gap-2 mt-4">
                <button id="editSaveBtn" class="bg-blue-600 text-white px-4 py-2 rounded">Save Changes</button>
                <button id="editDeleteBtn" class="bg-red-600 text-white px-4 py-2 rounded">Delete Shift</button>
            </div>
            <input type="hidden" id="editShiftId" value="">
        <div class="mt-2 text-xs text-yellow-700 bg-yellow-100 rounded px-2 py-1">⚠ Warning: Edit creates overlap with another shift</div>
    </div>
</div>

<script>
// Calendar week logic
document.addEventListener('DOMContentLoaded', function() {
    // We will track the START of the current week (Monday) in this variable.
    // Initialize from server `startDate` so client actions target the same week the server rendered.
    // Fallback to the current date if `startDate` is not available.
    let currentWeekStart;
    try {
        // server-provided startDate is in YYYY-MM-DD format
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

    // --- Utility Functions ---

    /**
     * Finds the Monday (start) of the week for any given date.
     */
    function getMonday(date) {
        let d = new Date(date);
        let day = d.getDay(); 
        // JavaScript day 0 is Sunday. We want Monday to be the start (1).
        // If it's Sunday (0), subtract 6 days. Otherwise, subtract day - 1.
        let diff = d.getDate() - day + (day === 0 ? -6 : 1); 
        d.setDate(diff);
        d.setHours(0, 0, 0, 0); // Reset time to midnight
        return d;
    }

    /**
     * Formats the current date range and updates the header element.
     */
    function updateWeekHeader() {
        // Calculate Week End (6 days after the stored start date)
        let weekEnd = new Date(currentWeekStart);
        weekEnd.setDate(currentWeekStart.getDate() + 6);

        // Formatting options
        const monthOptions = { month: 'short' };
        const dayOptions = { day: 'numeric' };
        const yearOptions = { year: 'numeric' };

        // Format parts
        const startMonth = currentWeekStart.toLocaleDateString('en-US', monthOptions).toUpperCase().replace('.', '');
        const startDay = currentWeekStart.toLocaleDateString('en-US', dayOptions);
        
        const endDay = weekEnd.toLocaleDateString('en-US', dayOptions);
        const endYear = weekEnd.toLocaleDateString('en-US', yearOptions);
        
        // Update the HTML element with the desired format: MMM DD - DD, YYYY
        weekRangeElement.textContent = `${startMonth} ${startDay} - ${endDay}, ${endYear}`;
    }

    /**
     * Changes the current week by a given number of days (+7 or -7).
     */
    function changeWeek(direction) {
        // This is the critical line: we are modifying the reference date (currentWeekStart) 
        // which will be used in the next updateWeekHeader() call.
        currentWeekStart.setDate(currentWeekStart.getDate() + direction);
        // Format date as YYYY-MM-DD for query param
        function pad(n){ return n<10 ? '0'+n : n }
        const y = currentWeekStart.getFullYear();
        const m = pad(currentWeekStart.getMonth() + 1);
        const d = pad(currentWeekStart.getDate());
        const qs = '?week_start=' + `${y}-${m}-${d}`;
        // Reload the page so server renders the timetable for the selected week
        window.location.href = window.location.pathname + qs;
    }

    // --- Event Listeners ---
    
    // Initial display update
    updateWeekHeader();

    // Previous Week (-7 days)
    if (prevWeekBtn) {
        prevWeekBtn.addEventListener('click', function() {
            changeWeek(-7);
        });
    }

    // Next Week (+7 days)
    if (nextWeekBtn) {
        nextWeekBtn.addEventListener('click', function() {
            changeWeek(7);
        });
    }
    
    // --- Day selector (UI) ---
    const daySelector = document.getElementById('daySelector');
    // Track selected days as a Set of strings (mon, tue, ...)
    const selectedDays = new Set();

    if (daySelector) {
        daySelector.addEventListener('click', function(e) {
            const btn = e.target.closest('.day-toggle');
            if (!btn) return;
            const day = btn.getAttribute('data-day');
            if (selectedDays.has(day)) {
                selectedDays.delete(day);
                btn.classList.remove('bg-blue-600', 'text-white');
                btn.classList.add('bg-blue-100');
            } else {
                selectedDays.add(day);
                btn.classList.remove('bg-blue-100');
                btn.classList.add('bg-blue-600', 'text-white');
            }
        });
    }

    // --- Employee search & auto-fill department ---
    // Build a small lookup for users and their departments using the staff collection (ensures department is available)
            const users = [
        @foreach($staff as $s)
            { id: '{{ $s->user->id }}', name: '{{ addslashes($s->user->name) }}', email: '{{ addslashes($s->user->email) }}', department: '{{ addslashes($s->department ?? '') }}' },
        @endforeach
    ];

    const assignSearch = document.getElementById('assignSearch');
    const assignUsersSelect = document.getElementById('assignUsers');
    const assignDepartment = document.getElementById('assignDepartment');
    const assignRestDayCheckbox = document.getElementById('assignRestDay');

    function filterUserOptions(query) {
        const q = query.trim().toLowerCase();
        // Clear options
        assignUsersSelect.innerHTML = '';
        const matches = users.filter(u => (u.name + ' ' + u.email).toLowerCase().includes(q));
        matches.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.name + ' <' + u.email + '>';
            assignUsersSelect.appendChild(opt);
        });

        // If exactly one match, auto-set department (if available)
        if (matches.length === 1 && matches[0].department) {
            // Try to set assignDepartment to staff's department if exists in options
            const deptVal = matches[0].department;
            let found = false;
            for (let i = 0; i < assignDepartment.options.length; i++) {
                if (assignDepartment.options[i].value.toLowerCase() === deptVal.toLowerCase()) {
                    assignDepartment.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                // Insert department as first option and select it
                const opt = document.createElement('option');
                opt.value = deptVal;
                opt.textContent = deptVal;
                assignDepartment.insertBefore(opt, assignDepartment.firstChild);
                assignDepartment.selectedIndex = 0;
            }
        }
    }

    if (assignSearch) {
        assignSearch.addEventListener('input', function(e) {
            filterUserOptions(e.target.value);
        });
        // Initialize with empty query to populate list
        filterUserOptions('');
    }

    // Toggle time/break inputs when Rest Day is checked
    if (assignRestDayCheckbox) {
        assignRestDayCheckbox.addEventListener('change', function() {
            const rest = assignRestDayCheckbox.checked;
            const startEl = document.getElementById('assignStartTime');
            const endEl = document.getElementById('assignEndTime');
            const breakEl = document.getElementById('assignBreakMinutes');
            if (startEl) startEl.disabled = rest;
            if (endEl) endEl.disabled = rest;
            if (breakEl) breakEl.disabled = rest;
        });
    }

    // Assign Shifts button (AJAX behavior)
    // This will create one shift per selected user per selected day by POSTing to /admin/shifts
    const assignShiftsBtnAjax = document.getElementById('assignShiftsBtn');
    if (assignShiftsBtnAjax) {
        assignShiftsBtnAjax.addEventListener('click', async function() {
            if (selectedDays.size === 0) {
                alert('Please select at least one day to assign shifts.');
                return;
            }

            // Gather selected users
            const userSelect = document.getElementById('assignUsers');
            const selectedUserIds = Array.from(userSelect.selectedOptions).map(o => o.value);
            if (selectedUserIds.length === 0) {
                alert('Please select at least one employee to assign shifts.');
                return;
            }

            const startTime = document.getElementById('assignStartTime').value;
            const endTime = document.getElementById('assignEndTime').value;
            const breakMinutes = document.getElementById('assignBreakMinutes').value || 0;
            const departmentInput = document.getElementById('assignDepartment').value;
            const isRestDay = (document.getElementById('assignRestDay') && document.getElementById('assignRestDay').checked) ? true : false;

            if (!isRestDay && (!startTime || !endTime)) {
                alert('Please provide both start and end times (or check Rest Day).');
                return;
            }

            // Map day key to offset from currentWeekStart (mon=0..sun=6)
            const dayIndexMap = { 'mon': 0, 'tue': 1, 'wed': 2, 'thu': 3, 'fri': 4, 'sat': 5, 'sun': 6 };

            const csrfMetaEl = document.querySelector('meta[name="csrf-token"]');
            const csrfToken = csrfMetaEl ? csrfMetaEl.getAttribute('content') : '';

            // Build bulk payload
            const shiftsPayload = [];
            selectedUserIds.forEach(userId => {
                selectedDays.forEach(dayKey => {
                    const offset = dayIndexMap[dayKey];
                    const dateObj = new Date(currentWeekStart);
                    dateObj.setDate(currentWeekStart.getDate() + offset);
                    // Format as local YYYY-MM-DD to avoid UTC timezone shift from toISOString()
                    const y = dateObj.getFullYear();
                    const m = (dateObj.getMonth() + 1).toString().padStart(2, '0');
                    const d = dateObj.getDate().toString().padStart(2, '0');
                    const dateStr = `${y}-${m}-${d}`;

                    const userLookup = users.find(u => String(u.id) === String(userId));
                    // Ensure department is never an empty string (server requires department)
                    let departmentForUser = 'General';
                    if (departmentInput && departmentInput.trim() !== '') {
                        departmentForUser = departmentInput;
                    } else if (userLookup && userLookup.department && String(userLookup.department).trim() !== '') {
                        departmentForUser = userLookup.department;
                    }

                    shiftsPayload.push({
                        user_id: userId,
                        department: departmentForUser,
                        date: dateStr,
                        // for rest day we send empty times and a rest_day flag
                        start_time: isRestDay ? '' : startTime,
                        end_time: isRestDay ? '' : endTime,
                        break_minutes: isRestDay ? 0 : (parseInt(breakMinutes) || 0),
                        rest_day: isRestDay,
                    });
                });
            });

            // Client-side overlap check (simple): ensure we don't assign a new shift where a shift already exists in table
            const conflicts = [];
            shiftsPayload.forEach(s => {
                const selector = `td[data-user-id="${s.user_id}"][data-date="${s.date}"]`;
                const cell = document.querySelector(selector);
                if (cell && !cell.classList.contains('bg-gray-50')) {
                    conflicts.push(`${s.date} for user ${s.user_id}`);
                }
            });
            if (conflicts.length) {
                alert('Cannot assign shifts because conflicts were detected:\n' + conflicts.join('\n'));
                return;
            }

                // Send individual POST requests to /admin/shifts (no bulk endpoint)
                try {
                    assignShiftsBtnAjax.disabled = true;
                    const statusEl = document.getElementById('assignStatus');
                    if (statusEl) statusEl.innerHTML = '';

                    const toast = document.getElementById('toast');
                    if (toast) { toast.textContent = 'Assigning shifts...'; toast.classList.remove('hidden'); }

                    // Send requests sequentially so we can mark each cell loading and update progressively
                    const results = [];
                    for (const s of shiftsPayload) {
                        const selector = `td[data-user-id="${s.user_id}"][data-date="${s.date}"]`;
                        const cell = document.querySelector(selector);
                        if (cell) cell.classList.add('cell-loading');

                        try {
                            const res = await fetch('/admin/shifts', {
                                method: 'POST',
                                credentials: 'same-origin',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                body: JSON.stringify(s),
                            });
                            const json = await res.json().catch(() => ({}));
                            results.push({ ok: res.ok, json, s });
                        } catch (e) {
                            results.push({ ok: false, json: { message: e.message }, s });
                        } finally {
                            if (cell) cell.classList.remove('cell-loading');
                        }
                    }

                    const successItems = results.filter(r => r.ok && r.json && r.json.success).map(r => ({ resp: r.json.shift || r.json, payload: r.s }));
                    const failures = results.filter(r => !(r.ok && r.json && r.json.success));

                    // Update table for successes and set data attributes so modal works
                    successItems.forEach(item => {
                        const sResp = item.resp || {};
                        const sPayload = item.payload || {};
                        // Try to find the cell by server-returned date first, fall back to payload date
                        const dateToUse = sResp.date || sPayload.date;
                        const userIdToUse = sResp.user_id || sPayload.user_id;
                        const selector = `td[data-user-id="${userIdToUse}"][data-date="${dateToUse}"]`;
                        let cell = document.querySelector(selector);

                        // If not found (server returned a different date), try the payload-based selector
                        if (!cell && sPayload.date && sPayload.user_id) {
                            const fallbackSel = `td[data-user-id="${sPayload.user_id}"][data-date="${sPayload.date}"]`;
                            cell = document.querySelector(fallbackSel);
                        }

                        if (cell) {
                            const start_time = sResp.start_time || sPayload.start_time || '';
                            const end_time = sResp.end_time || sPayload.end_time || '';
                            const break_minutes = (sResp.break_minutes !== undefined) ? sResp.break_minutes : (sPayload.break_minutes || 0);
                            const rest_day = (sResp.rest_day !== undefined) ? sResp.rest_day : (sPayload.rest_day || false);

                            // Normalize existing classes then add appropriate highlight
                            try { cell.classList.remove('bg-gray-50','bg-green-100','bg-yellow-100'); } catch(e) { /* ignore */ }
                            if (rest_day) {
                                cell.classList.add('bg-yellow-100');
                                cell.innerHTML = `<span class="font-semibold text-red-600">REST DAY</span>`;
                            } else {
                                cell.classList.add('bg-green-100');
                                cell.innerHTML = `${start_time} - ${end_time}<br><span class="text-xs text-gray-500">Break: ${break_minutes || 0} min</span>`;
                            }
                            // set data attributes so clicking opens modal with correct values
                            try {
                                if (sResp.id) cell.setAttribute('data-shift-id', sResp.id);
                                if (sResp.date) cell.setAttribute('data-date', sResp.date);
                                cell.setAttribute('data-start_time', start_time);
                                cell.setAttribute('data-end_time', end_time);
                                cell.setAttribute('data-break_minutes', break_minutes || '');
                                // derive department from users lookup (staff list) if available
                                const userLookup = users.find(u => String(u.id) === String(userIdToUse));
                                const deptVal = (userLookup && userLookup.department) ? userLookup.department : (sResp.department || sPayload.department || '');
                                cell.setAttribute('data-department', deptVal);
                                cell.setAttribute('data-rest_day', rest_day ? '1' : '0');
                            } catch (e) {
                                console.debug('failed to set data attrs', e);
                            }
                        }
                    });

                    if (failures.length > 0) {
                        console.error('Some shifts failed:', failures);
                        // Build readable failure messages
                        const msgs = failures.map(f => {
                            const uid = f.s.user_id;
                            const dt = f.s.date;
                            const status = f.json && f.json.message ? f.json.message : (f.ok === false ? 'request failed' : 'unknown error');
                            return `User ${uid} on ${dt}: ${status}`;
                        });
                        alert(`Assigned ${successItems.length} shifts. ${failures.length} failed:\n` + msgs.join('\n'));
                        if (toast) { toast.textContent = 'Partial'; setTimeout(()=>toast.classList.add('hidden'), 2000); }
                    } else {
                        alert(`Assigned ${successItems.length} shifts successfully.`);
                        if (toast) { toast.textContent = 'Done'; setTimeout(()=>toast.classList.add('hidden'), 1200); }
                    }

                } catch (err) {
                    console.error(err);
                    alert('Error assigning shifts. See console for details.');
                    const toast = document.getElementById('toast'); if (toast) { toast.textContent = 'Error'; setTimeout(()=>toast.classList.add('hidden'), 2000); }
                } finally {
                    assignShiftsBtnAjax.disabled = false;
                }
        });
    }

    // When opening the edit shift modal via table cell onclick, pre-select the day pill
    // The function now accepts the clicked TD element and reads data-* attributes from it.
    window.openEditShiftModal = function(td) {
        if (!td || !td.getAttribute) return;
        const userId = td.getAttribute('data-user-id');
        const dateStr = td.getAttribute('data-date');
        const shiftId = td.getAttribute('data-shift-id') || '';
        const startTime = td.getAttribute('data-start_time') || '';
        const endTime = td.getAttribute('data-end_time') || '';
        const breakMinutes = td.getAttribute('data-break_minutes') || '';
    const restDayFlag = td.getAttribute('data-rest_day') || '0';
        const department = td.getAttribute('data-department') || (document.getElementById('assignDepartment') ? document.getElementById('assignDepartment').value : 'General');

        // Parse date and find weekday
        const d = new Date(dateStr + 'T00:00:00');
        const weekday = d.getDay(); // 0=Sun .. 6=Sat
        const dayMap = {1: 'mon', 2: 'tue', 3: 'wed', 4: 'thu', 5: 'fri', 6: 'sat', 0: 'sun'};
        const dayKey = dayMap[weekday];

        // Clear previous selections
        selectedDays.clear();
        document.querySelectorAll('#daySelector .day-toggle').forEach(b => {
            b.classList.remove('bg-blue-600', 'text-white');
            b.classList.add('bg-blue-100');
        });

        // Select the day for the clicked cell
        const btn = document.querySelector(`#daySelector .day-toggle[data-day="${dayKey}"]`);
        if (btn) {
            selectedDays.add(dayKey);
            btn.classList.remove('bg-blue-100');
            btn.classList.add('bg-blue-600', 'text-white');
        }

        // Populate modal fields
        const modal = document.getElementById('editShiftModal');
        if (!modal) return;

        document.getElementById('editUserId').value = userId || '';
        document.getElementById('editDate').value = dateStr || '';
        document.getElementById('editShiftId').value = shiftId;

        document.getElementById('editStartTime').value = startTime;
        document.getElementById('editEndTime').value = endTime;
        document.getElementById('editBreakMinutes').value = breakMinutes || '';
        document.getElementById('editDepartment').value = department || '';

        // set assign panel rest checkbox based on cell
        try {
            const assignRest = document.getElementById('assignRestDay');
            if (assignRest) assignRest.checked = (String(restDayFlag) === '1' || String(restDayFlag) === 'true');
            // disable time fields if rest day
            const rest = assignRest && assignRest.checked;
            const assignStartEl = document.getElementById('assignStartTime');
            const assignEndEl = document.getElementById('assignEndTime');
            const assignBreakEl = document.getElementById('assignBreakMinutes');
            if (assignStartEl) assignStartEl.disabled = rest;
            if (assignEndEl) assignEndEl.disabled = rest;
            if (assignBreakEl) assignBreakEl.disabled = rest;
        } catch (e) { console.debug('failed to set assign rest checkbox', e); }

        modal.classList.remove('hidden');

        // Also populate the right-hand assign form so admin can quickly edit via the form
        try {
            // Department: try to select matching option, otherwise insert as first option
            const assignDeptEl = document.getElementById('assignDepartment');
            if (assignDeptEl) {
                let found = false;
                for (let i = 0; i < assignDeptEl.options.length; i++) {
                    if (assignDeptEl.options[i].value.toLowerCase() === (department || '').toLowerCase()) {
                        assignDeptEl.selectedIndex = i;
                        found = true;
                        break;
                    }
                }
                if (!found && department) {
                    const opt = document.createElement('option');
                    opt.value = department;
                    opt.textContent = department;
                    assignDeptEl.insertBefore(opt, assignDeptEl.firstChild);
                    assignDeptEl.selectedIndex = 0;
                }
            }

            // Users: select the clicked user in the multi-select
            const assignUsersEl = document.getElementById('assignUsers');
            if (assignUsersEl) {
                for (let i = 0; i < assignUsersEl.options.length; i++) {
                    assignUsersEl.options[i].selected = (String(assignUsersEl.options[i].value) === String(userId));
                }
            }

            // Times: strip seconds if present (HH:MM:SS -> HH:MM) for <input type=time>
            function stripSeconds(t) {
                if (!t) return '';
                if (t.length >= 5) return t.slice(0,5);
                return t;
            }
            const assignStart = document.getElementById('assignStartTime');
            const assignEnd = document.getElementById('assignEndTime');
            const assignBreak = document.getElementById('assignBreakMinutes');
            if (assignStart) assignStart.value = stripSeconds(startTime);
            if (assignEnd) assignEnd.value = stripSeconds(endTime);
            if (assignBreak) assignBreak.value = breakMinutes || '';

            // Ensure day pill selection mirrors the clicked cell (selectedDays already set above)
            // Scroll the assign panel into view so admin sees the populated form
            const assignPanel = document.getElementById('assignDepartment');
            if (assignPanel && assignPanel.scrollIntoView) {
                assignPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        } catch (e) {
            console.debug('Failed to populate assign form', e);
        }
        // ensure assignEditShiftId is set so assign-panel Edit can update this shift
        try {
            const assignEditShiftIdEl = document.getElementById('assignEditShiftId');
            if (assignEditShiftIdEl) assignEditShiftIdEl.value = shiftId || '';
        } catch (e) { console.debug('failed to set assignEditShiftId', e); }
    };

    // assign-panel Edit button behavior: update the selected shift using the assign panel values
    const assignEditBtn = document.getElementById('assignEditBtn');
    if (assignEditBtn) {
        assignEditBtn.addEventListener('click', async function() {
            const shiftId = document.getElementById('assignEditShiftId').value;
            if (!shiftId) {
                alert('No shift selected to edit. Click an existing shift cell first.');
                return;
            }

            // require exactly one user selected
            const selectedUserIds = Array.from(document.getElementById('assignUsers').selectedOptions).map(o => o.value);
            if (selectedUserIds.length !== 1) {
                alert('Please select exactly one employee to edit.');
                return;
            }

            // require exactly one day selected
            if (selectedDays.size !== 1) {
                alert('Please select exactly one day to edit.');
                return;
            }

            const startTime = document.getElementById('assignStartTime').value;
            const endTime = document.getElementById('assignEndTime').value;
            const breakMinutes = document.getElementById('assignBreakMinutes').value || 0;
            const departmentInput = document.getElementById('assignDepartment').value || 'General';
            const isRestDay = (document.getElementById('assignRestDay') && document.getElementById('assignRestDay').checked) ? true : false;

            if (!isRestDay && (!startTime || !endTime)) {
                alert('Please provide both start and end times (or check Rest Day).');
                return;
            }

            // compute date string for the selected day relative to currentWeekStart
            const dayIndexMap = { 'mon': 0, 'tue': 1, 'wed': 2, 'thu': 3, 'fri': 4, 'sat': 5, 'sun': 6 };
            const dayKey = Array.from(selectedDays)[0];
            const offset = dayIndexMap[dayKey];
            const dateObj = new Date(currentWeekStart);
            dateObj.setDate(currentWeekStart.getDate() + offset);
            const y = dateObj.getFullYear();
            const m = (dateObj.getMonth() + 1).toString().padStart(2, '0');
            const d = dateObj.getDate().toString().padStart(2, '0');
            const dateStr = `${y}-${m}-${d}`;

            const payload = {
                user_id: selectedUserIds[0],
                department: departmentInput,
                date: dateStr,
                start_time: isRestDay ? '' : startTime,
                end_time: isRestDay ? '' : endTime,
                break_minutes: isRestDay ? 0 : (parseInt(breakMinutes) || 0),
                rest_day: isRestDay,
            };

            try {
                assignEditBtn.disabled = true;
                const res = await fetch(`/admin/shifts/${shiftId}`, {
                    method: 'PUT',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json().catch(()=>({}));
                if (!res.ok || !json.success) {
                    alert(json.message || 'Failed to update shift');
                    console.error(json);
                    return;
                }

                const shift = json.shift || json;
                // update the corresponding table cell
                const selector = `td[data-user-id="${shift.user_id}"][data-date="${shift.date}"]`;
                const cell = document.querySelector(selector);
                if (cell) {
                    // clear old highlight classes
                    try { cell.classList.remove('bg-gray-50','bg-green-100','bg-yellow-100'); } catch(e) {}
                    const rest_day = shift.rest_day || false;
                    if (rest_day) {
                        cell.classList.add('bg-yellow-100');
                        cell.innerHTML = `<span class="font-semibold text-red-600">REST DAY</span>`;
                    } else {
                        cell.classList.add('bg-green-100');
                        cell.innerHTML = `${shift.start_time} - ${shift.end_time}<br><span class="text-xs text-gray-500">Break: ${shift.break_minutes || 0} min</span>`;
                    }
                    try {
                        cell.setAttribute('data-shift-id', shift.id || '');
                        cell.setAttribute('data-start_time', shift.start_time || '');
                        cell.setAttribute('data-end_time', shift.end_time || '');
                        cell.setAttribute('data-break_minutes', shift.break_minutes || '');
                        // department may not be returned by server; derive from users lookup
                        const userLookup2 = users.find(u => String(u.id) === String(shift.user_id || shift.user_id));
                        cell.setAttribute('data-department', (userLookup2 && userLookup2.department) ? userLookup2.department : (shift.department || ''));
                        cell.setAttribute('data-rest_day', rest_day ? '1' : '0');
                    } catch (e) { console.debug('failed to set cell attrs', e); }
                }

                alert('Shift updated successfully');
            } catch (err) {
                console.error(err);
                alert('Error updating shift');
            } finally {
                assignEditBtn.disabled = false;
            }
        });
    }

    // Edit modal Save / Delete handlers
    const editSaveBtn = document.getElementById('editSaveBtn');
    const editDeleteBtn = document.getElementById('editDeleteBtn');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    if (editSaveBtn) {
        editSaveBtn.addEventListener('click', async function() {
            const shiftId = document.getElementById('editShiftId').value;
            const userId = document.getElementById('editUserId').value;
            const date = document.getElementById('editDate').value;
            const start_time = document.getElementById('editStartTime').value;
            const end_time = document.getElementById('editEndTime').value;
            const break_minutes = document.getElementById('editBreakMinutes').value || 0;

            if (!userId || !date || !start_time || !end_time) {
                alert('Please fill user, date, start and end time.');
                return;
            }

            const payload = {
                user_id: userId,
                department: document.getElementById('editDepartment') ? document.getElementById('editDepartment').value : (document.getElementById('assignDepartment') ? document.getElementById('assignDepartment').value : 'General'),
                date: date,
                start_time: start_time,
                end_time: end_time,
                break_minutes: parseInt(break_minutes) || 0,
            };

            try {
                let url = '/admin/shifts';
                let method = 'POST';
                if (shiftId) { url = `/admin/shifts/${shiftId}`; method = 'PUT'; }

                editSaveBtn.disabled = true;
                const res = await fetch(url, {
                    method: method,
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.success) {
                    alert(json.message || 'Failed to save shift');
                    console.error(json);
                    return;
                }

                const shift = json.shift || (json.shifts && json.shifts[0]) || json;
                // update table cell
                const selector = `td[data-user-id="${shift.user_id}"][data-date="${shift.date}"]`;
                const cell = document.querySelector(selector);
                if (cell) {
                    cell.classList.remove('bg-gray-50');
                    cell.classList.add('bg-green-100');
                    cell.innerHTML = `${shift.start_time} - ${shift.end_time}<br><span class="text-xs text-gray-500">Break: ${shift.break_minutes || 0} min</span>`;
                    // ensure data attributes reflect new values
                    try {
                        cell.setAttribute('data-shift-id', shift.id || '');
                        cell.setAttribute('data-start_time', shift.start_time || '');
                        cell.setAttribute('data-end_time', shift.end_time || '');
                        cell.setAttribute('data-break_minutes', shift.break_minutes || '');
                        const userLookup3 = users.find(u => String(u.id) === String(shift.user_id || shift.user_id));
                        cell.setAttribute('data-department', (userLookup3 && userLookup3.department) ? userLookup3.department : (shift.department || ''));
                    } catch (e) {
                        console.debug('failed to set data attrs', e);
                    }
                }

                // close modal
                document.getElementById('editShiftModal').classList.add('hidden');
            } catch (err) {
                console.error(err);
                alert('Error saving shift');
            } finally {
                editSaveBtn.disabled = false;
            }
        });
    }

    if (editDeleteBtn) {
        editDeleteBtn.addEventListener('click', async function() {
            const shiftId = document.getElementById('editShiftId').value;
            if (!shiftId) {
                alert('No shift to delete.');
                return;
            }
            if (!confirm('Delete this shift?')) return;

            try {
                editDeleteBtn.disabled = true;
                const res = await fetch(`/admin/shifts/${shiftId}`, {
                    method: 'DELETE',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const json = await res.json().catch(() => ({}));
                if (!res.ok || !json.success) {
                    alert(json.message || 'Failed to delete shift');
                    return;
                }

                // clear cell
                const userId = document.getElementById('editUserId').value;
                const date = document.getElementById('editDate').value;
                const selector = `td[data-user-id="${userId}"][data-date="${date}"]`;
                const cell = document.querySelector(selector);
                if (cell) {
                    try { cell.classList.remove('bg-yellow-100','bg-green-100'); } catch(e) {}
                    cell.classList.add('bg-gray-50');
                    cell.innerHTML = '<span class="text-gray-400 italic">Add Shift</span>';
                }

                document.getElementById('editShiftModal').classList.add('hidden');
            } catch (err) {
                console.error(err);
                alert('Error deleting shift');
            } finally {
                editDeleteBtn.disabled = false;
            }
        });
    }

    // Close modal when clicking outside the modal content
    document.addEventListener('click', function(e) {
        const modal = document.getElementById('editShiftModal');
        if (!modal || modal.classList.contains('hidden')) return;
        const content = modal.querySelector('.bg-white');
        if (!content.contains(e.target)) {
            modal.classList.add('hidden');
        }
    });

    // (Old simple assign handler removed — AJAX handler runs earlier and reports status)
});
</script>
@endsection