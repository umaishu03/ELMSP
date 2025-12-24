<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon; // ✅ make sure Carbon is imported

class StaffTimetableController extends Controller
{
    public function index(Request $request)
    {
        // Get all staff users and their departments
        $staff = \App\Models\Staff::with('user')->get();
        
        // Define role order for sorting
        $roleOrder = ['manager', 'supervisor', 'cashier', 'kitchen', 'barista', 'waiter', 'joki'];
        
        // Sort staff by the defined role order
        $staff = $staff->sort(function($a, $b) use ($roleOrder) {
            $aDept = strtolower($a->department ?? '');
            $bDept = strtolower($b->department ?? '');
            
            $aIndex = array_search($aDept, $roleOrder);
            $bIndex = array_search($bDept, $roleOrder);
            
            // If role not found, put it at the end
            $aIndex = ($aIndex === false) ? count($roleOrder) : $aIndex;
            $bIndex = ($bIndex === false) ? count($roleOrder) : $bIndex;
            
            return $aIndex - $bIndex;
        });
        
        $users = \App\Models\User::where('role', 'staff')->get();

        /**
         * Determine week start (Monday)
         */
        $weekStart = $request->query('week_start')
            ? Carbon::parse($request->query('week_start'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $startDate = $weekStart->format('Y-m-d');

        // Build dates for the week (MON..SUN)
        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $weekStart->copy()->addDays($i)->format('Y-m-d');
        }

        /**
         * AUTO-COPY: if this week has no shifts, copy previous week's shifts
         */
        $existingCount = \App\Models\Shift::whereIn('date', $dates)->count();

        if ($existingCount == 0) {
            $prevWeekStart = $weekStart->copy()->subWeek();
            $prevDates = [];
            for ($i = 0; $i < 7; $i++) {
                $prevDates[] = $prevWeekStart->copy()->addDays($i)->format('Y-m-d');
            }

            $prevShifts = \App\Models\Shift::whereIn('date', $prevDates)->get();

            foreach ($prevShifts as $old) {
                $newDate = Carbon::parse($old->date)->addWeek()->format('Y-m-d');

                \App\Models\Shift::create([
                    'staff_id'      => $old->staff_id ?? null,
                    'date'          => $newDate,
                    'start_time'    => $old->start_time,
                    'end_time'      => $old->end_time,
                    'break_minutes' => $old->break_minutes,
                    'rest_day'      => $old->rest_day,
                ]);
            }
        }

        // Get all shifts for this week (eager-load staff, user, and leave relation)
        $shifts = \App\Models\Shift::whereIn('date', $dates)->with('staff.user', 'leave')->get();

        // Normalize shift->date to Y-m-d strings so view collection lookups by date work
        $shifts = $shifts->map(function($s) {
            try {
                if (isset($s->date) && ($s->date instanceof \Carbon\Carbon)) {
                    $s->date = $s->date->format('Y-m-d');
                } elseif (isset($s->date)) {
                    $s->date = date('Y-m-d', strtotime($s->date));
                }
            } catch (\Exception $e) {
                // If anything goes wrong, leave the original value (view will handle missing shifts)
            }
            return $s;
        });

        // Build a keyed map for quick lookup in the view: key = "{user_id}|{date}" => Shift
        $shiftsByKey = $shifts->keyBy(function($s) {
            $userId = '';
            if (isset($s->staff) && isset($s->staff->user)) {
                $userId = $s->staff->user->id;
            }
            return $userId . '|' . ($s->date ?? '');
        });

        return view('admin.staffTimetable', compact('staff', 'users', 'shifts', 'dates', 'startDate', 'shiftsByKey'));
    }

    /**
     * Staff-facing timetable: show only the authenticated staff user's shifts for the week.
     */
    public function staffIndex(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Determine week range like admin index
        $weekStart = $request->query('week_start')
            ? Carbon::parse($request->query('week_start'))->startOfWeek()
            : Carbon::now()->startOfWeek();

        $startDate = $weekStart->format('Y-m-d');

        $dates = [];
        for ($i = 0; $i < 7; $i++) {
            $dates[] = $weekStart->copy()->addDays($i)->format('Y-m-d');
        }

        // For staff-facing timetable we show all staff rows (read-only) but only admin-assigned shifts
        // Define role order for sorting
        $roleOrder = ['manager', 'supervisor', 'cashier', 'kitchen', 'barista', 'waiter', 'joki'];
        $staff = \App\Models\Staff::with('user')->get();
        
        // Sort staff by the defined role order
        $staff = $staff->sort(function($a, $b) use ($roleOrder) {
            $aDept = strtolower($a->department ?? '');
            $bDept = strtolower($b->department ?? '');
            
            $aIndex = array_search($aDept, $roleOrder);
            $bIndex = array_search($bDept, $roleOrder);
            
            // If role not found, put it at the end
            $aIndex = ($aIndex === false) ? count($roleOrder) : $aIndex;
            $bIndex = ($bIndex === false) ? count($roleOrder) : $bIndex;
            
            return $aIndex - $bIndex;
        });
        
        $users = \App\Models\User::where('role', 'staff')->get();

        // Get all shifts for this week (eager-load staff, user, and leave relation)
        $shifts = \App\Models\Shift::whereIn('date', $dates)->with('staff.user', 'leave')->get();

        // Normalize dates
        $shifts = $shifts->map(function($s) {
            try {
                if (isset($s->date) && ($s->date instanceof \Carbon\Carbon)) {
                    $s->date = $s->date->format('Y-m-d');
                } elseif (isset($s->date)) {
                    $s->date = date('Y-m-d', strtotime($s->date));
                }
            } catch (\Exception $e) {
                // ignore
            }
            return $s;
        });

        $shiftsByKey = $shifts->keyBy(function($s) {
            $userId = '';
            if (isset($s->staff) && isset($s->staff->user)) {
                $userId = $s->staff->user->id;
            }
            return $userId . '|' . ($s->date ?? '');
        });

        return view('staff.timetable', compact('staff', 'users', 'shifts', 'dates', 'startDate', 'shiftsByKey'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'rest_day' => 'nullable|boolean',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'break_minutes' => 'nullable|integer',
        ]);

        // If not a rest day, ensure times are provided
        $isRest = isset($data['rest_day']) && $data['rest_day'];
        if (!$isRest) {
            $request->validate([
                'start_time' => 'required',
                'end_time' => 'required',
            ]);
        } else {
            // ensure times are null/empty
            $data['start_time'] = '';
            $data['end_time'] = '';
            $data['break_minutes'] = 0;
        }

        // Map incoming user_id -> staff_id and create shift
        $staff = \App\Models\Staff::where('user_id', $data['user_id'])->first();
        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Staff record not found for user'], 422);
        }
        $payload = $data;
        // Remove department if present in payload from client-side
        if (isset($payload['department'])) unset($payload['department']);
        $payload['staff_id'] = $staff->id;
        unset($payload['user_id']);

        // create without day_of_week column (some installations may not have this column)
        $shift = \App\Models\Shift::create($payload);
        // normalize date for JSON response
        try {
            if (isset($shift->date)) {
                $shift->date = date('Y-m-d', strtotime($shift->date));
            }
        } catch (\Exception $e) {
            // ignore
        }
        return response()->json(['success' => true, 'shift' => $shift]);
    }

    /**
     * Create multiple shifts in one request.
     * Expected payload: { shifts: [ { user_id, department, date, day_of_week, start_time, end_time, break_minutes }, ... ] }
     */
    public function bulkStore(Request $request)
    {
        $data = $request->validate([
            'shifts' => 'required|array|min:1',
            'shifts.*.user_id' => 'required|exists:users,id',
            'shifts.*.date' => 'required|date',
            'shifts.*.rest_day' => 'nullable|boolean',
            'shifts.*.start_time' => 'nullable',
            'shifts.*.end_time' => 'nullable',
            'shifts.*.break_minutes' => 'nullable|integer',
        ]);

        $created = [];
        \DB::beginTransaction();
        try {
                foreach ($data['shifts'] as $s) {
                    $rest = isset($s['rest_day']) && $s['rest_day'];

                    // map user -> staff
                    $staff = \App\Models\Staff::where('user_id', $s['user_id'])->first();
                    if (!$staff) {
                        throw new \Exception('Staff record not found for user_id: ' . ($s['user_id'] ?? '')); 
                    }

                    $payload = [
                        'staff_id' => $staff->id,
                        'date' => $s['date'],
                        'start_time' => $rest ? '' : ($s['start_time'] ?? ''),
                        'end_time' => $rest ? '' : ($s['end_time'] ?? ''),
                        'break_minutes' => $rest ? 0 : ($s['break_minutes'] ?? 0),
                        'rest_day' => $rest,
                    ];
                    $shift = \App\Models\Shift::create($payload);
                    $created[] = $shift;
                }
            \DB::commit();
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to create shifts', 'error' => $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'shifts' => $created]);
    }

    public function update(Request $request, $id)
    {
        $shift = \App\Models\Shift::findOrFail($id);
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'rest_day' => 'nullable|boolean',
            'start_time' => 'nullable',
            'end_time' => 'nullable',
            'break_minutes' => 'nullable|integer',
        ]);
        $isRest = isset($data['rest_day']) && $data['rest_day'];
        if (!$isRest) {
            $request->validate([
                'start_time' => 'required',
                'end_time' => 'required',
            ]);
        } else {
            $data['start_time'] = '';
            $data['end_time'] = '';
            $data['break_minutes'] = 0;
        }

        // Map incoming user_id -> staff_id for update
        $staff = \App\Models\Staff::where('user_id', $data['user_id'])->first();
        if (!$staff) {
            return response()->json(['success' => false, 'message' => 'Staff record not found for user'], 422);
        }
        $payload = $data;
        if (isset($payload['department'])) unset($payload['department']);
        $payload['staff_id'] = $staff->id;
        unset($payload['user_id']);

        $shift->update($payload);
        // normalize date for JSON response
        try {
            if (isset($shift->date)) {
                $shift->date = date('Y-m-d', strtotime($shift->date));
            }
        } catch (\Exception $e) {
            // ignore
        }
        return response()->json(['success' => true, 'shift' => $shift]);
    }

    public function destroy($id)
    {
        $shift = \App\Models\Shift::findOrFail($id);
        $shift->delete();
        return response()->json(['success' => true]);
    }
}
