<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Overtime;
use App\Models\OTClaim;

class OvertimeController extends Controller
{
    public function apply()
    {
        $user = Auth::user();
        $department = $user->staff?->department ?? null;
        
        return view('staff.overtime.applyOt', compact('department'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ot_type' => 'required|in:fulltime,public_holiday',
            'ot_date' => 'required|date',
            'hours' => 'required|numeric|min:0.5',
            'ot_reason' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $staff = $user->staff;
        if (!$staff) {
            return back()->with('error', 'Staff record not found for user');
        }

        // Create temporary OT instance for validation
        $overtime = new Overtime([
            'staff_id' => $staff->id,
            'ot_type' => $request->ot_type,
            'ot_date' => $request->ot_date,
            'hours' => $request->hours,
            'remarks' => $request->ot_reason,
        ]);

        // Check weekly limit before creating
        $weeklyLimitCheck = Overtime::checkWeeklyLimit($overtime);
        if (!$weeklyLimitCheck['valid']) {
            return back()->with('error', $weeklyLimitCheck['message'])->withInput();
        }

        // Create the OT record
        $overtime->save();

        return redirect()->route('staff.statusOt')->with('success', 'Overtime application submitted and awaits admin approval.');
    }

    public function claim()
    {
        $user = Auth::user();

        $staff = $user->staff;
        $availableOT = collect();
        if ($staff) {
            // approved & unclaimed overtimes for this staff
            $availableOT = Overtime::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->where(function($q){ $q->where('claimed', false)->orWhereNull('claimed'); })
                ->orderBy('ot_date', 'desc')
                ->get();
        }

        return view('staff.overtime.claimOt', compact('availableOT'));
    }

    public function claimStore(Request $request)
    {
        $request->validate([
            'claim_type' => 'required|in:replacement_leave,payroll',
            'selected_overtimes' => 'required|array|min:1',
            'selected_overtimes.*' => 'integer|exists:overtimes,id',
        ]);

        $user = Auth::user();
        $staff = $user->staff;

        $otIds = $request->selected_overtimes;
        $ots = collect();
        if ($staff) {
            $ots = Overtime::whereIn('id', $otIds)->where('staff_id', $staff->id)->where('status','approved')->get();
        }

        $fulltime_hours = $ots->where('ot_type','fulltime')->sum('hours');
        $public_holiday_hours = $ots->where('ot_type','public_holiday')->sum('hours');

        $replacement_days = floor(($fulltime_hours + $public_holiday_hours) / 8);

        $claim = OTClaim::create([
            'user_id' => $user->id,
            'claim_type' => $request->claim_type,
            'ot_ids' => $otIds,
            'fulltime_hours' => $fulltime_hours,
            'public_holiday_hours' => $public_holiday_hours,
            'replacement_days' => $replacement_days,
            'status' => 'pending',
        ]);

        // mark individual OT records as claimed (they remain approved)
        Overtime::whereIn('id', $otIds)->update(['claimed' => true]);

        return redirect()->route('staff.claimOt')->with('success', 'OT claim submitted and awaiting admin approval.');
    }

    public function status()
    {
        $user = Auth::user();

        $overtimes = collect();
        $staff = $user->staff;
        if ($staff) {
            $overtimes = Overtime::where('staff_id', $staff->id)
                ->orderBy('ot_date', 'desc')
                ->get();
        }

        return view('staff.overtime.statusOt', compact('overtimes'));
    }

    /**
     * Check weekly OT limit for the selected date
     */
    public function checkWeeklyLimit(Request $request)
    {
        $request->validate([
            'ot_date' => 'required|date',
        ]);

        $user = Auth::user();
        $staff = $user->staff;
        if (!$staff) {
            return response()->json([
                'valid' => false,
                'message' => 'Staff record not found'
            ], 400);
        }

        // Create temporary OT instance for validation
        $overtime = new Overtime([
            'staff_id' => $staff->id,
            'ot_date' => $request->ot_date,
        ]);

        $weeklyLimitCheck = Overtime::checkWeeklyLimit($overtime);
        
        return response()->json($weeklyLimitCheck);
    }
        
}
