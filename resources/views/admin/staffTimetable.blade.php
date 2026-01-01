@extends('layouts.admin')
@section('title', 'Staff Timetable')
@section('content')
@section('content')
<!-- Breadcrumbs -->
<div class="mb-6">
    {!! \App\Helpers\BreadcrumbHelper::render() !!}
</div>
<div class="mb-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-2">Staff Timetable</h1>
</div>

{{-- Toast Notification Container --}}
<div id="toast-container" class="fixed top-4 right-4 z-50 space-y-2" style="max-width: 400px;"></div>
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
                                   data-department="{{ $shift->staff->department ?? $staffMember->department ?? '' }}"
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
                    <option>Manager</option>
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
                <input id="assignStartTime" type="time" min="06:00" max="23:00" class="w-full px-3 py-2 border rounded-lg" value=""> 
                <p class="text-xs text-gray-500 mt-1">Valid time: 6:00 AM - 11:00 PM</p>
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">End Time:</label>
                <input id="assignEndTime" type="time" min="06:00" max="23:00" class="w-full px-3 py-2 border rounded-lg" value=""> 
                <p class="text-xs text-gray-500 mt-1">Valid time: 6:00 AM - 11:00 PM</p>
            </div>
            <div class="mb-2">
                <label class="block text-xs font-semibold mb-1">Break (mins):</label>
                <input id="assignBreakMinutes" type="number" min="0" class="w-full px-3 py-2 border rounded-lg" value="60">
            </div>
            <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="text-xs font-semibold text-blue-800 mb-1">Normal Working Hours:</div>
                <div id="assignNormalHoursNote" class="text-xs text-blue-700">
                    Manager & Supervisor: 12 hours<br>
                    Other Staff: 7.5 hours
                </div>
            </div>
            <div class="mb-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
                <div class="text-xs font-semibold text-gray-700 mb-1">Calculated Hours:</div>
                <div id="assignCalculatedHours" class="text-xs text-gray-600 font-semibold">
                    Enter start and end time to calculate
                </div>
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
            <div id="toast" class="fixed top-6 right-6 z-50 px-6 py-4 rounded-lg shadow-2xl transform transition-all duration-300 ease-in-out hidden">
                <div class="flex items-center gap-3">
                    <div id="toastIcon" class="text-2xl"></div>
                    <div>
                        <div id="toastMessage" class="font-semibold text-white"></div>
                        <div id="toastSubMessage" class="text-sm text-white opacity-90 mt-1"></div>
                    </div>
                </div>
            </div>
            <style>
                .cell-loading { position:relative; }
                .cell-loading:after { content:''; position:absolute; right:6px; top:6px; width:10px; height:10px; border-radius:50%; border:2px solid rgba(255,255,255,0.4); border-top-color:#fff; animation:spin 0.8s linear infinite; }
                @keyframes spin { to { transform: rotate(360deg); } }
                #toast.show { transform: translateX(0) scale(1); opacity: 1; }
                #toast.hidden { transform: translateX(400px) scale(0.9); opacity: 0; }
            </style>
        </div>
    </div>
</div>

<div id="editShiftModal" class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md">
        <h2 class="text-lg font-bold mb-4">EDIT SHIFT</h2>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">Start Time:</label>
            <input id="editStartTime" type="time" min="06:00" max="23:00" placeholder="Start Time" class="w-full px-3 py-2 border rounded-lg" value="">
            <p class="text-xs text-gray-500 mt-1">Valid time: 6:00 AM - 11:00 PM</p>
        </div>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">End Time:</label>
            <input id="editEndTime" type="time" min="06:00" max="23:00" placeholder="End Time" class="w-full px-3 py-2 border rounded-lg" value="">
            <p class="text-xs text-gray-500 mt-1">Valid time: 6:00 AM - 11:00 PM</p>
        </div>
        <div class="mb-2">
            <label class="block text-xs font-semibold mb-1">Break (mins):</label>
            <input id="editBreakMinutes" type="number" placeholder="Break (mins)" min="0" class="w-full px-3 py-2 border rounded-lg" value="60">
        </div>
        <div class="mb-2 p-2 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="text-xs font-semibold text-blue-800 mb-1">Normal Working Hours:</div>
            <div id="editNormalHoursNote" class="text-xs text-blue-700">
                Manager & Supervisor: 12 hours<br>
                Other Staff: 7.5 hours
            </div>
        </div>
        <div class="mb-2 p-2 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="text-xs font-semibold text-gray-700 mb-1">Calculated Hours:</div>
            <div id="editCalculatedHours" class="text-xs text-gray-600 font-semibold">
                Enter start and end time to calculate
            </div>
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

    // --- Toast Notification System ---
    function showToast(message, subMessage = '', type = 'success') {
        const toast = document.getElementById('toast');
        const toastMessage = document.getElementById('toastMessage');
        const toastSubMessage = document.getElementById('toastSubMessage');
        const toastIcon = document.getElementById('toastIcon');
        
        if (!toast || !toastMessage) return;
        
        // Set message
        toastMessage.textContent = message;
        if (toastSubMessage) {
            toastSubMessage.textContent = subMessage;
            toastSubMessage.style.display = subMessage ? 'block' : 'none';
        }
        
        // Set icon and color based on type
        if (toastIcon) {
            if (type === 'success') {
                toastIcon.textContent = '✓';
                toast.classList.remove('bg-red-600', 'bg-yellow-600', 'bg-blue-600');
                toast.classList.add('bg-green-600');
            } else if (type === 'error') {
                toastIcon.textContent = '✕';
                toast.classList.remove('bg-green-600', 'bg-yellow-600', 'bg-blue-600');
                toast.classList.add('bg-red-600');
            } else if (type === 'warning') {
                toastIcon.textContent = '⚠';
                toast.classList.remove('bg-green-600', 'bg-red-600', 'bg-blue-600');
                toast.classList.add('bg-yellow-600');
            } else {
                toastIcon.textContent = 'ℹ';
                toast.classList.remove('bg-green-600', 'bg-red-600', 'bg-yellow-600');
                toast.classList.add('bg-blue-600');
            }
        }
        
        // Show toast with animation
        toast.classList.remove('hidden');
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.classList.add('hidden');
            }, 300);
        }, 3000);
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

    function filterUserOptions(query, departmentFilter) {
        const q = (query || '').trim().toLowerCase();
        const deptFilter = (departmentFilter || '').trim();
        
        // Clear options
        assignUsersSelect.innerHTML = '';
        
        // Filter by both search query and department
        let matches = users;
        
        // Filter by department if selected
        if (deptFilter) {
            matches = matches.filter(u => {
                const userDept = (u.department || '').trim();
                return userDept.toLowerCase() === deptFilter.toLowerCase();
            });
        }
        
        // Filter by search query if provided
        if (q) {
            matches = matches.filter(u => (u.name + ' ' + u.email).toLowerCase().includes(q));
        }
        
        // Populate the select with filtered results
        matches.forEach(u => {
            const opt = document.createElement('option');
            opt.value = u.id;
            opt.textContent = u.name;
            assignUsersSelect.appendChild(opt);
        });

        // If exactly one match, auto-set department (if available and not already set)
        if (matches.length === 1 && matches[0].department && !deptFilter) {
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

    // Function to update employee list based on current filters
    function updateEmployeeList() {
        const searchQuery = assignSearch ? assignSearch.value : '';
        const selectedDept = assignDepartment ? assignDepartment.value : '';
        filterUserOptions(searchQuery, selectedDept);
    }

    if (assignSearch) {
        assignSearch.addEventListener('input', function(e) {
            updateEmployeeList();
        });
    }
    
    // Filter employees when department changes
    if (assignDepartment) {
        assignDepartment.addEventListener('change', function() {
            updateEmployeeList();
            // Update hours calculation when department changes
            updateAssignHoursCalculation();
        });
    }
    
    // Initialize with empty query to populate list
    updateEmployeeList();

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
            // Update calculation when rest day is toggled
            updateAssignHoursCalculation();
        });
    }

    // Function to calculate total shift duration (end - start, including break)
    function calculateTotalShiftHours(startTime, endTime) {
        if (!startTime || !endTime) return null;
        
        const start = new Date('2000-01-01T' + startTime + ':00');
        let end = new Date('2000-01-01T' + endTime + ':00');
        
        // Handle overnight shifts (end time is next day)
        if (end <= start) {
            end.setDate(end.getDate() + 1);
        }
        
        const diffMs = end - start;
        const totalHours = diffMs / (1000 * 60 * 60);
        
        return totalHours;
    }
    
    // Function to calculate expected total shift duration (normal work hours + break)
    function calculateExpectedTotalHours(normalWorkHours, breakMinutes) {
        const breakHours = (parseInt(breakMinutes) || 0) / 60;
        return normalWorkHours + breakHours;
    }

    // Function to get normal hours based on department
    function getNormalHours(department) {
        const dept = (department || '').toLowerCase().trim();
        if (dept === 'manager' || dept === 'supervisor') {
            return 12;
        }
        return 7.5;
    }

    // Function to format hours display
    function formatHours(hours) {
        if (hours === null || isNaN(hours)) return 'N/A';
        const h = Math.floor(hours);
        const m = Math.round((hours - h) * 60);
        if (m === 0) {
            return `${h} hours`;
        }
        return `${h}h ${m}m`;
    }

    // Function to validate time is within 6:00 AM - 11:00 PM
    function validateTimeRange(timeString) {
        if (!timeString) return { valid: false, message: 'Time is required' };
        
        const [hours, minutes] = timeString.split(':').map(Number);
        const totalMinutes = hours * 60 + minutes;
        
        const minMinutes = 6 * 60; // 6:00 AM = 360 minutes
        const maxMinutes = 23 * 60; // 11:00 PM = 1380 minutes (23:00)
        
        if (totalMinutes < minMinutes || totalMinutes > maxMinutes) {
            return {
                valid: false,
                message: `Time must be between 6:00 AM and 11:00 PM. You entered ${timeString}`
            };
        }
        
        return { valid: true };
    }

    // Update assign panel hours calculation
    function updateAssignHoursCalculation() {
        const startTime = document.getElementById('assignStartTime').value;
        const endTime = document.getElementById('assignEndTime').value;
        const breakMinutes = document.getElementById('assignBreakMinutes').value || 0;
        const department = document.getElementById('assignDepartment').value;
        const isRestDay = assignRestDayCheckbox && assignRestDayCheckbox.checked;
        const calcDisplay = document.getElementById('assignCalculatedHours');
        const normalHoursNote = document.getElementById('assignNormalHoursNote');
        
        if (!calcDisplay) return;
        
        // Update normal hours note based on department
        if (normalHoursNote) {
            const normalHours = getNormalHours(department);
            if (department && department.toLowerCase().trim() === 'manager') {
                normalHoursNote.innerHTML = 'Manager: <strong>12 hours</strong><br>Other Staff: 7.5 hours';
            } else if (department && department.toLowerCase().trim() === 'supervisor') {
                normalHoursNote.innerHTML = 'Supervisor: <strong>12 hours</strong><br>Other Staff: 7.5 hours';
            } else {
                normalHoursNote.innerHTML = 'Manager & Supervisor: 12 hours<br>Other Staff: <strong>7.5 hours</strong>';
            }
        }
        
        if (isRestDay) {
            calcDisplay.textContent = 'Rest Day - No hours calculated';
            calcDisplay.className = 'text-xs text-gray-500 italic';
            return;
        }
        
        const totalShiftHours = calculateTotalShiftHours(startTime, endTime);
        if (totalShiftHours === null) {
            calcDisplay.textContent = 'Enter start and end time to calculate';
            calcDisplay.className = 'text-xs text-gray-600 font-semibold';
        } else {
            const normalWorkHours = getNormalHours(department);
            const breakHours = (parseInt(breakMinutes) || 0) / 60;
            const expectedTotalHours = calculateExpectedTotalHours(normalWorkHours, breakMinutes);
            const hoursFormatted = formatHours(totalShiftHours);
            const diff = totalShiftHours - expectedTotalHours;
            
            // Show work hours and break separately
            const workHours = totalShiftHours - breakHours;
            const workHoursFormatted = formatHours(workHours);
            
            if (Math.abs(diff) < 0.1) {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-green-600">✓ Matches expected: ${formatHours(normalWorkHours)} work + ${formatHours(breakHours)} break</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            } else if (diff > 0) {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-orange-600">+${formatHours(diff)} over expected (${formatHours(expectedTotalHours)})</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            } else {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-red-600">${formatHours(Math.abs(diff))} under expected (${formatHours(expectedTotalHours)})</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            }
        }
    }

    // Update edit modal hours calculation
    function updateEditHoursCalculation() {
        const startTime = document.getElementById('editStartTime').value;
        const endTime = document.getElementById('editEndTime').value;
        const breakMinutes = document.getElementById('editBreakMinutes').value || 0;
        const department = document.getElementById('editDepartment').value;
        const calcDisplay = document.getElementById('editCalculatedHours');
        const normalHoursNote = document.getElementById('editNormalHoursNote');
        
        if (!calcDisplay) return;
        
        // Update normal hours note based on department
        if (normalHoursNote) {
            const normalHours = getNormalHours(department);
            if (department && department.toLowerCase().trim() === 'manager') {
                normalHoursNote.innerHTML = 'Manager: <strong>12 hours</strong><br>Other Staff: 7.5 hours';
            } else if (department && department.toLowerCase().trim() === 'supervisor') {
                normalHoursNote.innerHTML = 'Supervisor: <strong>12 hours</strong><br>Other Staff: 7.5 hours';
            } else {
                normalHoursNote.innerHTML = 'Manager & Supervisor: 12 hours<br>Other Staff: <strong>7.5 hours</strong>';
            }
        }
        
        const totalShiftHours = calculateTotalShiftHours(startTime, endTime);
        if (totalShiftHours === null) {
            calcDisplay.textContent = 'Enter start and end time to calculate';
            calcDisplay.className = 'text-xs text-gray-600 font-semibold';
        } else {
            const normalWorkHours = getNormalHours(department);
            const breakHours = (parseInt(breakMinutes) || 0) / 60;
            const expectedTotalHours = calculateExpectedTotalHours(normalWorkHours, breakMinutes);
            const hoursFormatted = formatHours(totalShiftHours);
            const diff = totalShiftHours - expectedTotalHours;
            
            // Show work hours and break separately
            const workHours = totalShiftHours - breakHours;
            const workHoursFormatted = formatHours(workHours);
            
            if (Math.abs(diff) < 0.1) {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-green-600">✓ Matches expected: ${formatHours(normalWorkHours)} work + ${formatHours(breakHours)} break</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            } else if (diff > 0) {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-orange-600">+${formatHours(diff)} over expected (${formatHours(expectedTotalHours)})</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            } else {
                calcDisplay.innerHTML = `Total: ${hoursFormatted} (Work: ${workHoursFormatted} + Break: ${formatHours(breakHours)})<br><span class="text-red-600">${formatHours(Math.abs(diff))} under expected (${formatHours(expectedTotalHours)})</span>`;
                calcDisplay.className = 'text-xs text-gray-600 font-semibold';
            }
        }
    }

    // Add event listeners for assign panel time inputs
    const assignStartTimeEl = document.getElementById('assignStartTime');
    const assignEndTimeEl = document.getElementById('assignEndTime');
    const assignBreakMinutesEl = document.getElementById('assignBreakMinutes');
    
    if (assignStartTimeEl) {
        assignStartTimeEl.addEventListener('input', updateAssignHoursCalculation);
        assignStartTimeEl.addEventListener('change', updateAssignHoursCalculation);
    }
    if (assignEndTimeEl) {
        assignEndTimeEl.addEventListener('input', updateAssignHoursCalculation);
        assignEndTimeEl.addEventListener('change', updateAssignHoursCalculation);
    }
    if (assignBreakMinutesEl) {
        assignBreakMinutesEl.addEventListener('input', updateAssignHoursCalculation);
        assignBreakMinutesEl.addEventListener('change', updateAssignHoursCalculation);
    }
    if (assignDepartment) {
        assignDepartment.addEventListener('change', updateAssignHoursCalculation);
    }

    // Add event listeners for edit modal time inputs
    const editStartTimeEl = document.getElementById('editStartTime');
    const editEndTimeEl = document.getElementById('editEndTime');
    const editBreakMinutesEl = document.getElementById('editBreakMinutes');
    const editDepartmentEl = document.getElementById('editDepartment');
    
    if (editStartTimeEl) {
        editStartTimeEl.addEventListener('input', updateEditHoursCalculation);
        editStartTimeEl.addEventListener('change', updateEditHoursCalculation);
    }
    if (editEndTimeEl) {
        editEndTimeEl.addEventListener('input', updateEditHoursCalculation);
        editEndTimeEl.addEventListener('change', updateEditHoursCalculation);
    }
    if (editBreakMinutesEl) {
        editBreakMinutesEl.addEventListener('input', updateEditHoursCalculation);
        editBreakMinutesEl.addEventListener('change', updateEditHoursCalculation);
    }
    if (editDepartmentEl) {
        editDepartmentEl.addEventListener('input', updateEditHoursCalculation);
        editDepartmentEl.addEventListener('change', updateEditHoursCalculation);
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

            // Validate time ranges (6:00 AM - 11:00 PM)
            if (!isRestDay) {
                const startValidation = validateTimeRange(startTime);
                if (!startValidation.valid) {
                    alert(startValidation.message);
                    return;
                }
                
                const endValidation = validateTimeRange(endTime);
                if (!endValidation.valid) {
                    alert(endValidation.message);
                    return;
                }
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

                    showToast('Assigning shifts...', 'Please wait while we update the timetable', 'info');

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
                        showToast(
                            `Partially Successful: ${successItems.length} shifts assigned`,
                            `${failures.length} shift(s) failed. Timetable updated.`,
                            'warning'
                        );
                        // Still show alert for detailed error info
                        setTimeout(() => {
                            alert(`Assigned ${successItems.length} shifts. ${failures.length} failed:\n` + msgs.join('\n'));
                        }, 500);
                    } else {
                        showToast(
                            'Shifts Assigned Successfully!',
                            `${successItems.length} shift(s) assigned. Timetable has been updated.`,
                            'success'
                        );
                    }

                } catch (err) {
                    console.error(err);
                    showToast(
                        'Error Assigning Shifts',
                        'An error occurred. Please check the console for details.',
                        'error'
                    );
                    setTimeout(() => {
                        alert('Error assigning shifts. See console for details.');
                    }, 500);
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
        
        // Get department: first from cell data, then from user lookup, then fallback
        let department = td.getAttribute('data-department') || '';
        if (!department && userId) {
            const userLookup = users.find(u => String(u.id) === String(userId));
            if (userLookup && userLookup.department) {
                department = userLookup.department;
            }
        }
        if (!department) {
            department = (document.getElementById('assignDepartment') ? document.getElementById('assignDepartment').value : '') || 'General';
        }

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
                
                // Filter employee list based on selected department
                // Use setTimeout to ensure the department is set first, then filter
                setTimeout(() => {
                    updateEmployeeList();
                    
                    // After filtering, select the clicked user in the multi-select
                    const assignUsersEl = document.getElementById('assignUsers');
                    if (assignUsersEl) {
                        // Clear previous selections first
                        for (let i = 0; i < assignUsersEl.options.length; i++) {
                            assignUsersEl.options[i].selected = false;
                        }
                        // Select the clicked user
                        for (let i = 0; i < assignUsersEl.options.length; i++) {
                            if (String(assignUsersEl.options[i].value) === String(userId)) {
                                assignUsersEl.options[i].selected = true;
                                break;
                            }
                        }
                    }
                }, 50);
            } else {
                // If department element doesn't exist, just select the user
                const assignUsersEl = document.getElementById('assignUsers');
                if (assignUsersEl) {
                    for (let i = 0; i < assignUsersEl.options.length; i++) {
                        assignUsersEl.options[i].selected = (String(assignUsersEl.options[i].value) === String(userId));
                    }
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
        
        // Update hours calculation after populating modal
        setTimeout(() => {
            updateEditHoursCalculation();
            updateAssignHoursCalculation();
        }, 100);
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

            // Validate time ranges (6:00 AM - 11:00 PM)
            if (!isRestDay) {
                const startValidation = validateTimeRange(startTime);
                if (!startValidation.valid) {
                    alert(startValidation.message);
                    return;
                }
                
                const endValidation = validateTimeRange(endTime);
                if (!endValidation.valid) {
                    alert(endValidation.message);
                    return;
                }
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
                
                // Store original cell info to check if date/user changed
                const originalCell = document.querySelector(`td[data-shift-id="${shiftId}"]`);
                const originalDate = originalCell ? originalCell.getAttribute('data-date') : dateStr;
                const originalUserId = originalCell ? originalCell.getAttribute('data-user-id') : selectedUserIds[0];
                
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
                    showToast(
                        'Error Updating Shift',
                        json.message || 'Failed to update shift',
                        'error'
                    );
                    console.error(json);
                    assignEditBtn.disabled = false;
                    return;
                }

                const shift = json.shift || json;
                
                // Use the same pattern as assign shift - try server date first, then fallback to payload date
                const dateToUse = shift.date || dateStr;
                const userIdToUse = shift.user_id || selectedUserIds[0];
                
                // If date or user changed, clear the old cell
                if (originalCell && (dateToUse !== originalDate || String(userIdToUse) !== String(originalUserId))) {
                    originalCell.classList.remove('bg-green-100', 'bg-yellow-100', 'bg-red-100');
                    originalCell.classList.add('bg-gray-50');
                    originalCell.innerHTML = '<span class="text-gray-400 italic">Add Shift</span>';
                    originalCell.removeAttribute('data-shift-id');
                    originalCell.setAttribute('data-start_time', '');
                    originalCell.setAttribute('data-end_time', '');
                    originalCell.setAttribute('data-break_minutes', '');
                    originalCell.setAttribute('data-rest_day', '0');
                }
                
                // Find the cell using the same pattern as assign shift
                const selector = `td[data-user-id="${userIdToUse}"][data-date="${dateToUse}"]`;
                let cell = document.querySelector(selector);
                
                // If not found, try with the original date (fallback like assign shift does)
                if (!cell && dateToUse !== dateStr) {
                    const fallbackSel = `td[data-user-id="${userIdToUse}"][data-date="${dateStr}"]`;
                    cell = document.querySelector(fallbackSel);
                }
                
                // If still not found, try by shift-id
                if (!cell && shift.id) {
                    cell = document.querySelector(`td[data-shift-id="${shift.id}"]`);
                }
                
                // If still not found, try by original shift-id
                if (!cell && shiftId) {
                    cell = document.querySelector(`td[data-shift-id="${shiftId}"]`);
                }
                
                if (cell) {
                    const start_time = shift.start_time || '';
                    const end_time = shift.end_time || '';
                    const break_minutes = (shift.break_minutes !== undefined) ? shift.break_minutes : 0;
                    const rest_day = (shift.rest_day !== undefined) ? shift.rest_day : false;
                    
                    // Normalize existing classes then add appropriate highlight (same as assign shift)
                    try { 
                        cell.classList.remove('bg-gray-50', 'bg-green-100', 'bg-yellow-100', 'bg-red-100'); 
                    } catch(e) { /* ignore */ }
                    
                    if (rest_day) {
                        cell.classList.add('bg-yellow-100');
                        cell.innerHTML = `<span class="font-semibold text-red-600">REST DAY</span>`;
                    } else {
                        cell.classList.add('bg-green-100');
                        cell.innerHTML = `${start_time} - ${end_time}<br><span class="text-xs text-gray-500">Break: ${break_minutes || 0} min</span>`;
                    }
                    
                    // Set data attributes (same pattern as assign shift)
                    try {
                        if (shift.id) cell.setAttribute('data-shift-id', shift.id);
                        if (shift.date) cell.setAttribute('data-date', shift.date);
                        cell.setAttribute('data-start_time', start_time);
                        cell.setAttribute('data-end_time', end_time);
                        cell.setAttribute('data-break_minutes', break_minutes || '');
                        // derive department from users lookup (staff list) if available
                        const userLookup2 = users.find(u => String(u.id) === String(userIdToUse));
                        const deptVal = (userLookup2 && userLookup2.department) ? userLookup2.department : (shift.department || '');
                        cell.setAttribute('data-department', deptVal);
                        cell.setAttribute('data-rest_day', rest_day ? '1' : '0');
                    } catch (e) { 
                        console.debug('failed to set cell attrs', e); 
                    }
                } else {
                    console.warn('Cell not found for shift update. Selector:', selector, 'Shift data:', shift);
                    showToast(
                        'Shift Updated',
                        'Please refresh to see changes',
                        'warning'
                    );
                }

                showToast(
                    'Shift Updated Successfully!',
                    'The shift has been updated and the timetable refreshed.',
                    'success'
                );
            } catch (err) {
                console.error(err);
                showToast(
                    'Error Updating Shift',
                    'An error occurred while updating the shift.',
                    'error'
                );
                setTimeout(() => {
                    alert('Error updating shift');
                }, 500);
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

            // Get rest_day status from assign panel checkbox (since modal doesn't have one)
            const assignRestDayCheckbox = document.getElementById('assignRestDay');
            const isRestDay = assignRestDayCheckbox && assignRestDayCheckbox.checked;

            if (!userId || !date) {
                alert('Please fill user and date.');
                return;
            }

            if (!isRestDay && (!start_time || !end_time)) {
                alert('Please fill start and end time (or check Rest Day).');
                return;
            }

            // Validate time ranges (6:00 AM - 11:00 PM) only if not rest day
            if (!isRestDay) {
                const startValidation = validateTimeRange(start_time);
                if (!startValidation.valid) {
                    alert(startValidation.message);
                    return;
                }
                
                const endValidation = validateTimeRange(end_time);
                if (!endValidation.valid) {
                    alert(endValidation.message);
                    return;
                }
            }

            const payload = {
                user_id: userId,
                department: document.getElementById('editDepartment') ? document.getElementById('editDepartment').value : (document.getElementById('assignDepartment') ? document.getElementById('assignDepartment').value : 'General'),
                date: date,
                start_time: isRestDay ? '' : start_time,
                end_time: isRestDay ? '' : end_time,
                break_minutes: isRestDay ? 0 : (parseInt(break_minutes) || 0),
                rest_day: isRestDay,
            };

            try {
                let url = '/admin/shifts';
                let method = 'POST';
                if (shiftId) { url = `/admin/shifts/${shiftId}`; method = 'PUT'; }

                editSaveBtn.disabled = true;
                
                // Store original date to check if it changed
                const originalDate = date;
                const originalUserId = userId;
                
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
                    showToast(
                        'Error Saving Shift',
                        json.message || 'Failed to save shift',
                        'error'
                    );
                    console.error(json);
                    editSaveBtn.disabled = false;
                    return;
                }

                const shift = json.shift || (json.shifts && json.shifts[0]) || json;
                
                // Use the same pattern as assign shift - try server date first, then fallback to payload date
                const dateToUse = shift.date || date;
                const userIdToUse = shift.user_id || userId;
                
                // If date or user changed, clear the old cell (only if we have shiftId)
                if (shiftId && (dateToUse !== originalDate || String(userIdToUse) !== String(originalUserId))) {
                    const oldSelector = `td[data-user-id="${originalUserId}"][data-date="${originalDate}"]`;
                    const oldCell = document.querySelector(oldSelector);
                    if (oldCell) {
                        oldCell.classList.remove('bg-green-100', 'bg-yellow-100', 'bg-red-100');
                        oldCell.classList.add('bg-gray-50');
                        oldCell.innerHTML = '<span class="text-gray-400 italic">Add Shift</span>';
                        oldCell.removeAttribute('data-shift-id');
                        oldCell.setAttribute('data-start_time', '');
                        oldCell.setAttribute('data-end_time', '');
                        oldCell.setAttribute('data-break_minutes', '');
                        oldCell.setAttribute('data-rest_day', '0');
                    }
                }
                
                // Find the cell using the same pattern as assign shift
                const selector = `td[data-user-id="${userIdToUse}"][data-date="${dateToUse}"]`;
                let cell = document.querySelector(selector);
                
                // If not found, try with the original date (fallback like assign shift does)
                if (!cell && dateToUse !== date) {
                    const fallbackSel = `td[data-user-id="${userIdToUse}"][data-date="${date}"]`;
                    cell = document.querySelector(fallbackSel);
                }
                
                // If still not found, try by shift-id
                if (!cell && shift.id) {
                    cell = document.querySelector(`td[data-shift-id="${shift.id}"]`);
                }
                
                // If still not found, try by original shift-id
                if (!cell && shiftId) {
                    cell = document.querySelector(`td[data-shift-id="${shiftId}"]`);
                }
                
                if (cell) {
                    const start_time = shift.start_time || '';
                    const end_time = shift.end_time || '';
                    const break_minutes = (shift.break_minutes !== undefined) ? shift.break_minutes : 0;
                    const rest_day = (shift.rest_day !== undefined) ? shift.rest_day : false;
                    
                    // Normalize existing classes then add appropriate highlight (same as assign shift)
                    try { 
                        cell.classList.remove('bg-gray-50', 'bg-green-100', 'bg-yellow-100', 'bg-red-100'); 
                    } catch(e) { /* ignore */ }
                    
                    if (rest_day) {
                        cell.classList.add('bg-yellow-100');
                        cell.innerHTML = `<span class="font-semibold text-red-600">REST DAY</span>`;
                    } else {
                        cell.classList.add('bg-green-100');
                        cell.innerHTML = `${start_time} - ${end_time}<br><span class="text-xs text-gray-500">Break: ${break_minutes || 0} min</span>`;
                    }
                    
                    // Set data attributes (same pattern as assign shift)
                    try {
                        if (shift.id) cell.setAttribute('data-shift-id', shift.id);
                        if (shift.date) cell.setAttribute('data-date', shift.date);
                        cell.setAttribute('data-start_time', start_time);
                        cell.setAttribute('data-end_time', end_time);
                        cell.setAttribute('data-break_minutes', break_minutes || '');
                        // derive department from users lookup (staff list) if available
                        const userLookup3 = users.find(u => String(u.id) === String(userIdToUse));
                        const deptVal = (userLookup3 && userLookup3.department) ? userLookup3.department : (shift.department || '');
                        cell.setAttribute('data-department', deptVal);
                        cell.setAttribute('data-rest_day', rest_day ? '1' : '0');
                    } catch (e) {
                        console.debug('failed to set data attrs', e);
                    }
                } else {
                    console.warn('Cell not found for shift update. Selector:', selector, 'Shift data:', shift);
                    showToast(
                        'Shift Updated',
                        'Please refresh to see changes',
                        'warning'
                    );
                }

                // close modal
                document.getElementById('editShiftModal').classList.add('hidden');
                showToast(
                    'Shift Saved Successfully!',
                    'The shift has been saved and the timetable updated.',
                    'success'
                );
            } catch (err) {
                console.error(err);
                showToast(
                    'Error Saving Shift',
                    'An error occurred while saving the shift.',
                    'error'
                );
                setTimeout(() => {
                    alert('Error saving shift');
                }, 500);
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
                showToast(
                    'Shift Deleted Successfully!',
                    'The shift has been removed and the timetable updated.',
                    'success'
                );
            } catch (err) {
                console.error(err);
                showToast(
                    'Error Deleting Shift',
                    'An error occurred while deleting the shift.',
                    'error'
                );
                setTimeout(() => {
                    alert('Error deleting shift');
                }, 500);
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

// Toast Notification System
function showToast(message, type = 'success') {
    const container = document.getElementById('toast-container');
    if (!container) return;
    
    const toast = document.createElement('div');
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const iconColor = type === 'success' ? 'text-green-500' : 'text-red-500';
    const icon = type === 'success' 
        ? '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>'
        : '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path></svg>';
    
    toast.className = `${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center gap-3 animate-slide-in-right transform transition-all duration-300`;
    toast.innerHTML = `
        <div class="flex-shrink-0 ${iconColor} bg-white rounded-full p-1">
            ${icon}
        </div>
        <div class="flex-1">
            <p class="font-semibold text-sm">${type === 'success' ? 'Success!' : 'Error!'}</p>
            <p class="text-sm opacity-90">${message}</p>
        </div>
        <button onclick="this.parentElement.remove()" class="flex-shrink-0 hover:opacity-75 transition">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
        </button>
    `;
    
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        setTimeout(() => toast.remove(), 300);
    }, 5000);
}

// Check for flash messages on page load
document.addEventListener('DOMContentLoaded', function() {
    @if(session('success'))
        showToast('{{ session('success') }}', 'success');
    @endif
    
    @if(session('error'))
        showToast('{{ session('error') }}', 'error');
    @endif
});

// Add CSS animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in-right {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    .animate-slide-in-right {
        animation: slide-in-right 0.3s ease-out;
    }
`;
document.head.appendChild(style);
</script>
@endsection