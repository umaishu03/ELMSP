<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Overtime;
use App\Models\OTClaim;
use App\Models\Leave;
use Carbon\Carbon;

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

        // Check per-person weekly limit before creating
        $weeklyLimitCheck = Overtime::checkWeeklyLimit($overtime);
        if (!$weeklyLimitCheck['valid']) {
            return back()->with('error', $weeklyLimitCheck['message'])->withInput();
        }

        // Check department weekly limit before creating
        $departmentLimitCheck = Overtime::checkDepartmentWeeklyLimit($overtime);
        if (!$departmentLimitCheck['valid']) {
            return back()->with('error', $departmentLimitCheck['message'])->withInput();
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
        $excludedOT = collect();
        $excludedHours = 0;
        $futureOT = collect();
        $futureHours = 0;
        
        if ($staff) {
            $today = Carbon::today();
            
            // Get all approved & unclaimed overtimes for this staff
            $allOT = Overtime::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->where(function($q){ $q->where('claimed', false)->orWhereNull('claimed'); })
                ->orderBy('ot_date', 'desc')
                ->get();

            // Get all approved leaves for this staff
            $approvedLeaves = Leave::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->get();

            // Filter overtime: exclude future dates and conflicts with approved leaves
            $availableOT = $allOT->filter(function($ot) use ($approvedLeaves, $today, &$excludedOT, &$excludedHours, &$futureOT, &$futureHours) {
                $otDate = Carbon::parse($ot->ot_date);
                
                // Exclude future dates (today or later)
                if ($otDate->greaterThanOrEqualTo($today)) {
                    $futureOT->push($ot);
                    $futureHours += $ot->hours;
                    return false; // Exclude future OT
                }
                
                // Check if this OT date falls within any approved leave period
                $hasConflict = $approvedLeaves->contains(function($leave) use ($otDate) {
                    $startDate = Carbon::parse($leave->start_date);
                    $endDate = Carbon::parse($leave->end_date);
                    return $otDate->between($startDate, $endDate, true); // inclusive
                });

                if ($hasConflict) {
                    $excludedOT->push($ot);
                    $excludedHours += $ot->hours;
                    return false; // Exclude this OT
                }

                return true; // Include this OT (past date, no leave conflict)
            });

            // Re-index the collection
            $availableOT = $availableOT->values();
        }

        return view('staff.overtime.claimOt', compact('availableOT', 'excludedOT', 'excludedHours', 'futureOT', 'futureHours'));
    }

    public function claimStore(Request $request)
    {
        $validationRules = [
            'claim_type' => 'required|in:replacement_leave,payroll',
            'selected_overtimes' => 'required|array|min:1',
            'selected_overtimes.*' => 'integer|exists:overtimes,id',
        ];
        
        // For payroll claims, validate the hours inputs
        if ($request->claim_type === 'payroll') {
            $validationRules['fulltime_hours'] = 'nullable|numeric|min:0';
            $validationRules['public_holiday_hours'] = 'nullable|numeric|min:0';
        } else {
            // For replacement leave, validate days_to_claim
            $validationRules['days_to_claim'] = 'required|integer|min:1';
        }
        
        $request->validate($validationRules);
        
        // Additional validation for payroll: at least one hour type must be claimed
        if ($request->claim_type === 'payroll') {
            $fulltimeHours = (float) ($request->input('fulltime_hours', 0));
            $publicHolidayHours = (float) ($request->input('public_holiday_hours', 0));
            
            if ($fulltimeHours == 0 && $publicHolidayHours == 0) {
                return back()->with('error', 'Please enter at least one hour type to claim (fulltime or public holiday).');
            }
        }

        $user = Auth::user();
        $staff = $user->staff;

        $otIds = $request->selected_overtimes;
        $ots = collect();
        if ($staff) {
            $today = Carbon::today();
            
            $ots = Overtime::whereIn('id', $otIds)->where('staff_id', $staff->id)->where('status','approved')->get();
            
            // Validate: Reject any OT for future dates (today or later)
            $futureOT = $ots->filter(function($ot) use ($today) {
                $otDate = Carbon::parse($ot->ot_date);
                return $otDate->greaterThanOrEqualTo($today);
            });

            if ($futureOT->count() > 0) {
                $futureDates = $futureOT->pluck('ot_date')
                    ->map(function($date) {
                        return Carbon::parse($date)->format('M d, Y');
                    })
                    ->unique()
                    ->implode(', ');
                
                return back()->with('error', "Cannot claim overtime for future dates. Overtime can only be claimed for dates that have already passed. Future dates detected: {$futureDates}.");
            }
            
            // Double-check: Exclude any OT that conflicts with approved leaves
            $approvedLeaves = Leave::where('staff_id', $staff->id)
                ->where('status', 'approved')
                ->get();

            $conflictedOT = $ots->filter(function($ot) use ($approvedLeaves) {
                $otDate = Carbon::parse($ot->ot_date);
                return $approvedLeaves->contains(function($leave) use ($otDate) {
                    $startDate = Carbon::parse($leave->start_date);
                    $endDate = Carbon::parse($leave->end_date);
                    return $otDate->between($startDate, $endDate, true);
                });
            });

            if ($conflictedOT->count() > 0) {
                $conflictedDates = $conflictedOT->pluck('ot_date')
                    ->map(function($date) {
                        return Carbon::parse($date)->format('M d, Y');
                    })
                    ->unique()
                    ->implode(', ');
                
                return back()->with('error', "Cannot claim overtime on dates where you have approved leave: {$conflictedDates}. These hours have been automatically excluded.");
            }
        }

        // For payroll claims, use the hours from form inputs
        // For replacement leave, calculate from all selected OT records
        if ($request->claim_type === 'payroll') {
            $requestedFulltimeHours = (float) ($request->input('fulltime_hours', 0));
            $requestedPublicHolidayHours = (float) ($request->input('public_holiday_hours', 0));
            
            // Validate that claimed hours don't exceed available hours
            $availableFulltime = $ots->where('ot_type', 'fulltime')->sum('hours');
            $availablePublicHoliday = $ots->where('ot_type', 'public_holiday')->sum('hours');
            
            if ($requestedFulltimeHours > $availableFulltime) {
                return back()->with('error', "Cannot claim {$requestedFulltimeHours} fulltime hours. Only {$availableFulltime} hours available.");
            }
            
            if ($requestedPublicHolidayHours > $availablePublicHoliday) {
                return back()->with('error', "Cannot claim {$requestedPublicHolidayHours} public holiday hours. Only {$availablePublicHoliday} hours available.");
            }
            
            // Select only the OT records needed to fulfill the requested hours
            // Note: OT records are atomic - you can't claim partial hours from a single record
            $selectedOtIds = [];
            $fulltimeClaimed = 0;
            $publicHolidayClaimed = 0;
            
            // Process fulltime OT records first (oldest first to be consistent)
            foreach ($ots->where('ot_type', 'fulltime')->sortBy('ot_date') as $ot) {
                if ($fulltimeClaimed < $requestedFulltimeHours) {
                    $selectedOtIds[] = $ot->id;
                    $fulltimeClaimed += $ot->hours;
                    // Stop once we've reached or exceeded the requested hours
                    if ($fulltimeClaimed >= $requestedFulltimeHours) {
                        break;
                    }
                }
            }
            
            // Process public holiday OT records
            foreach ($ots->where('ot_type', 'public_holiday')->sortBy('ot_date') as $ot) {
                if ($publicHolidayClaimed < $requestedPublicHolidayHours) {
                    $selectedOtIds[] = $ot->id;
                    $publicHolidayClaimed += $ot->hours;
                    // Stop once we've reached or exceeded the requested hours
                    if ($publicHolidayClaimed >= $requestedPublicHolidayHours) {
                        break;
                    }
                }
            }
            
            // Validate that we can fulfill the request
            if ($requestedFulltimeHours > 0 && $fulltimeClaimed < $requestedFulltimeHours) {
                return back()->with('error', "Cannot claim {$requestedFulltimeHours} fulltime hours. Only {$fulltimeClaimed} hours available from selected overtime records.");
            }
            
            if ($requestedPublicHolidayHours > 0 && $publicHolidayClaimed < $requestedPublicHolidayHours) {
                return back()->with('error', "Cannot claim {$requestedPublicHolidayHours} public holiday hours. Only {$publicHolidayClaimed} hours available from selected overtime records.");
            }
            
            // Use the actual claimed hours (store what was actually claimed)
            // This may be slightly more than requested if the last OT record exceeds the needed amount
            // For example: if user wants 4 hours but only has an 8-hour record, we claim 8 hours
            $fulltime_hours = $fulltimeClaimed;
            $public_holiday_hours = $publicHolidayClaimed;
            $otIds = $selectedOtIds;
            
            $replacement_days = 0; // Not applicable for payroll
        } else {
            // Replacement leave: use all selected OT records
        $fulltime_hours = $ots->where('ot_type','fulltime')->sum('hours');
        $public_holiday_hours = $ots->where('ot_type','public_holiday')->sum('hours');
        $replacement_days = floor(($fulltime_hours + $public_holiday_hours) / 8);
        }

        $claim = OTClaim::create([
            'user_id' => $user->id,
            'claim_type' => $request->claim_type,
            'ot_ids' => $otIds,
            'fulltime_hours' => $fulltime_hours,
            'public_holiday_hours' => $public_holiday_hours,
            'replacement_days' => $replacement_days ?? 0,
            'status' => 'pending',
        ]);

        // mark individual OT records as claimed (they remain approved)
        // Only mark the OT records that are actually included in the claim
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
