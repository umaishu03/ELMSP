<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Leave;
use App\Models\OTClaim;
use Carbon\Carbon;

class LeaveController extends Controller
{
    public function application()
    {
        $user = Auth::user();
        $staff = $user->staff;
        $staffId = $staff ? $staff->id : null;

        // Get user's approved OT hours for replacement leave calculation via ot_ids
        $approvedOTClaims = collect();
        if ($staffId) {
            $overtimeIds = \App\Models\Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
            if (!empty($overtimeIds)) {
                $allPayrollClaims = OTClaim::where('claim_type', 'payroll')
                    ->approved()
                    ->get();
                
                $approvedOTClaims = $allPayrollClaims->filter(function($claim) use ($overtimeIds) {
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_string($claimOtIds)) {
                        $claimOtIds = json_decode($claimOtIds, true) ?? [];
                    }
                    return !empty(array_intersect($overtimeIds, $claimOtIds));
                });
            }
        }

        $totalOTHours = $approvedOTClaims->sum(function($claim) {
            return ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
        });

        // Get approved replacement leave claims to add to balance
        $approvedReplacementClaims = collect();
        if ($staffId) {
            $overtimeIds = \App\Models\Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
            if (!empty($overtimeIds)) {
                $allReplacementClaims = OTClaim::where('claim_type', 'replacement_leave')
                    ->where('status', 'approved')
                    ->get();
                
                $approvedReplacementClaims = $allReplacementClaims->filter(function($claim) use ($overtimeIds) {
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_string($claimOtIds)) {
                        $claimOtIds = json_decode($claimOtIds, true) ?? [];
                    }
                    return !empty(array_intersect($overtimeIds, $claimOtIds));
                });
            }
        }
        
        // Add replacement days from approved claims to total OT hours
        $replacementClaimDays = $approvedReplacementClaims->sum(function($claim) {
            return $claim->replacement_days ?? 0;
        });
        $replacementClaimHours = $replacementClaimDays * 8;
        $totalOTHours += $replacementClaimHours;

        // Build leave balance map by reading `leave_balances` for this staff when available.
        $leaveBalance = [];
        $leaveTypes = \App\Models\LeaveType::all()->keyBy('type_name');
        $balances = [];
        if ($staffId) {
            $balances = \App\Models\LeaveBalance::with('leaveType')
                ->where('staff_id', $staffId)
                ->get()
                ->filter(function($b){ return $b->leaveType !== null; })
                ->keyBy(fn($b) => strtolower($b->leaveType->type_name));
        }

        // helper to compute fallback if no leave_balances row exists
        $computeFallback = function($typeName) use ($staffId) {
            return $this->getLeaveBalance($staffId, $typeName);
        };

        $expectedTypes = ['annual','hospitalization','medical','emergency','replacement','marriage','unpaid'];
        foreach ($expectedTypes as $typeName) {
            $key = strtolower($typeName);
            // Always recalculate replacement leave from OT hours (don't use stored balance)
            if ($typeName === 'replacement') {
                $replacementType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('replacement')])->first();
                $max = floor($totalOTHours / 8);
                $taken = 0;
                if ($replacementType) {
                    $taken = Leave::where('staff_id', $staffId)
                        ->where('leave_type_id', $replacementType->id)
                        ->where('status', 'approved')
                        ->sum('total_days');
                }
                // Include approved replacement claim days in balance
                $replacementClaimDays = $approvedReplacementClaims->sum(function($claim) {
                    return $claim->replacement_days ?? 0;
                });
                $balance = max(0, $max - $taken);
                $leaveBalance[$typeName] = [
                    'max' => $max, 
                    'taken' => (float)$taken, 
                    'balance' => $balance,
                    'ot_hours' => $totalOTHours,
                    'approved_claims_days' => $replacementClaimDays
                ];
            } elseif (isset($balances[$key])) {
                $b = $balances[$key];
                // For unpaid leave, calculate taken directly from Leave records (not from leave_balances)
                if ($typeName === 'unpaid') {
                    $max = 10;
                    $unpaidType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
                    $taken = 0;
                    if ($unpaidType) {
                        $taken = Leave::where('staff_id', $staffId)
                            ->where('leave_type_id', $unpaidType->id)
                            ->where('status', 'approved')
                            ->sum('total_days');
                    }
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => (float)$taken,
                        'balance' => $balance,
                    ];
                } else {
                    // Recalculate taken from actual approved leaves to ensure accuracy
                    // This prevents negative values from incorrect total_days calculations
                    $leaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower($typeName)])->first();
                    $taken = 0;
                    if ($leaveType) {
                        $taken = Leave::where('staff_id', $staffId)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->get()
                            ->sum(function($leave) {
                                // Recalculate days if total_days is invalid
                                if ($leave->total_days <= 0 && $leave->start_date && $leave->end_date) {
                                    $startDate = Carbon::parse($leave->start_date)->startOfDay();
                                    $endDate = Carbon::parse($leave->end_date)->startOfDay();
                                    return abs($endDate->diffInDays($startDate)) + 1;
                                }
                                return max(0, $leave->total_days);
                            });
                    }
                    $max = (float)$b->total_days;
                    $taken = max(0, (float)$taken); // Ensure taken is never negative
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => $taken,
                        'balance' => $balance,
                    ];
                }
            } else {
                $leaveBalance[$typeName] = $computeFallback($typeName);
            }
        }

        // Get approved overtime dates for conflict checking
        $approvedOvertimeDates = [];
        if ($staffId) {
            $approvedOvertimes = \App\Models\Overtime::where('staff_id', $staffId)
                ->where('status', 'approved')
                ->get();
            
            $approvedOvertimeDates = $approvedOvertimes->pluck('ot_date')
                ->map(function($date) {
                    return $date->format('Y-m-d');
                })
                ->toArray();
        }

        $leaveTypes = \App\Models\LeaveType::orderBy('type_name')->get();
        return view('staff.leave.application', compact('leaveBalance', 'totalOTHours', 'leaveTypes', 'approvedOvertimeDates'));
    }

    public function status()
    {
        $user = Auth::user();
        $staff = $user->staff;
        $staffId = $staff ? $staff->id : null;

        // Get user's leave applications
        $leaveApplications = Leave::where('staff_id', $staffId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Build leave balance map using leave_balances where available, fall back to computed values
        $leaveBalance = [];
        $leaveTypes = \App\Models\LeaveType::all()->keyBy('type_name');
        $balances = [];
        if ($staffId) {
            $balances = \App\Models\LeaveBalance::with('leaveType')
                ->where('staff_id', $staffId)
                ->get()
                ->filter(function($b){ return $b->leaveType !== null; })
                ->keyBy(fn($b) => strtolower($b->leaveType->type_name));
        }

        // compute OT hours for replacement fallback via ot_ids
        $approvedOTClaims = collect();
        if ($staffId) {
            $overtimeIds = \App\Models\Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
            if (!empty($overtimeIds)) {
                $allPayrollClaims = OTClaim::where('claim_type', 'payroll')
                    ->approved()
                    ->get();
                
                $approvedOTClaims = $allPayrollClaims->filter(function($claim) use ($overtimeIds) {
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_string($claimOtIds)) {
                        $claimOtIds = json_decode($claimOtIds, true) ?? [];
                    }
                    return !empty(array_intersect($overtimeIds, $claimOtIds));
                });
            }
        }
        $totalOTHours = $approvedOTClaims->sum(function($claim) {
            return ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
        });

        // Get approved replacement leave claims to add to balance
        $approvedReplacementClaims = collect();
        if ($staffId) {
            $overtimeIds = \App\Models\Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
            if (!empty($overtimeIds)) {
                $allReplacementClaims = OTClaim::where('claim_type', 'replacement_leave')
                    ->where('status', 'approved')
                    ->get();
                
                $approvedReplacementClaims = $allReplacementClaims->filter(function($claim) use ($overtimeIds) {
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_string($claimOtIds)) {
                        $claimOtIds = json_decode($claimOtIds, true) ?? [];
                    }
                    return !empty(array_intersect($overtimeIds, $claimOtIds));
                });
            }
        }
        
        // Add replacement days from approved claims to total OT hours
        $replacementClaimDays = $approvedReplacementClaims->sum(function($claim) {
            return $claim->replacement_days ?? 0;
        });
        $replacementClaimHours = $replacementClaimDays * 8;
        $totalOTHours += $replacementClaimHours;

        $expectedTypes = ['annual','hospitalization','medical','emergency','replacement','marriage','unpaid'];
        foreach ($expectedTypes as $typeName) {
            $key = strtolower($typeName);
            // Always recalculate replacement leave from OT hours (don't use stored balance)
            if ($typeName === 'replacement') {
                $replacementType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('replacement')])->first();
                $max = floor($totalOTHours / 8);
                $taken = 0;
                if ($replacementType) {
                    $taken = Leave::where('staff_id', $staffId)
                        ->where('leave_type_id', $replacementType->id)
                        ->where('status', 'approved')
                        ->sum('total_days');
                }
                // Include approved replacement claim days in balance
                $replacementClaimDays = $approvedReplacementClaims->sum(function($claim) {
                    return $claim->replacement_days ?? 0;
                });
                $balance = max(0, $max - $taken);
                $leaveBalance[$typeName] = [
                    'max' => $max, 
                    'taken' => (float)$taken, 
                    'balance' => $balance,
                    'ot_hours' => $totalOTHours,
                    'approved_claims_days' => $replacementClaimDays
                ];
            } elseif (isset($balances[$key])) {
                $b = $balances[$key];
                // For unpaid leave, calculate taken directly from Leave records (not from leave_balances)
                if ($typeName === 'unpaid') {
                    $max = 10;
                    $unpaidType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('unpaid')])->first();
                    $taken = 0;
                    if ($unpaidType) {
                        $taken = Leave::where('staff_id', $staffId)
                            ->where('leave_type_id', $unpaidType->id)
                            ->where('status', 'approved')
                            ->sum('total_days');
                    }
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => (float)$taken,
                        'balance' => $balance,
                    ];
                } else {
                    // Recalculate taken from actual approved leaves to ensure accuracy
                    // This prevents negative values from incorrect total_days calculations
                    $leaveType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower($typeName)])->first();
                    $taken = 0;
                    if ($leaveType) {
                        $taken = Leave::where('staff_id', $staffId)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('status', 'approved')
                            ->get()
                            ->sum(function($leave) {
                                // Recalculate days if total_days is invalid
                                if ($leave->total_days <= 0 && $leave->start_date && $leave->end_date) {
                                    $startDate = Carbon::parse($leave->start_date)->startOfDay();
                                    $endDate = Carbon::parse($leave->end_date)->startOfDay();
                                    return abs($endDate->diffInDays($startDate)) + 1;
                                }
                                return max(0, $leave->total_days);
                            });
                    }
                    $max = (float)$b->total_days;
                    $taken = max(0, (float)$taken); // Ensure taken is never negative
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => $taken,
                        'balance' => $balance,
                    ];
                }
            } else {
                $leaveBalance[$typeName] = $this->getLeaveBalance($staffId, $typeName);
            }
        }

        // Calculate stats
        $stats = [
            'total' => $leaveApplications->count(),
            'pending' => $leaveApplications->where('status', 'pending')->count(),
            'approved' => $leaveApplications->where('status', 'approved')->count(),
            'rejected' => $leaveApplications->where('status', 'rejected')->count(),
        ];

        // Pass leave applications with rejection_reason for JSON encoding
        $leaveApplicationsForJson = $leaveApplications->map(function($leave) {
            $typeName = $leave->leaveType?->type_name ?? null;
            
            // Recalculate days if total_days is invalid (negative or zero when dates exist)
            $days = $leave->total_days;
            if ($days <= 0 && $leave->start_date && $leave->end_date) {
                $startDate = Carbon::parse($leave->start_date)->startOfDay();
                $endDate = Carbon::parse($leave->end_date)->startOfDay();
                $days = abs($endDate->diffInDays($startDate)) + 1;
            }
            
            return [
                'id' => $leave->id,
                'leaveType' => $typeName,
                'leaveTypeName' => $typeName ? ucfirst(str_replace('_', ' ', $typeName)) : null,
                'startDate' => $leave->start_date,
                'endDate' => $leave->end_date,
                'days' => $days,
                'reason' => $leave->reason,
                'status' => $leave->status,
                'appliedDate' => $leave->created_at->format('Y-m-d'),
                'autoApproved' => $leave->auto_approved ?? false,
                'approvedDate' => $leave->approved_at ? $leave->approved_at->format('Y-m-d') : null,
                'remarks' => $leave->remarks ?? null,
                'rejectionReason' => $leave->rejection_reason,
                'attachment' => $leave->attachment ? route('staff.leave.attachment', $leave->id) : null,
                'attachmentName' => $leave->attachment ? basename($leave->attachment) : null
            ];
        });

        return view('staff.leave.status', compact('leaveApplications', 'leaveBalance', 'stats', 'leaveApplicationsForJson'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Validate input (use leave_type_id FK)
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png|max:10240',
        ]);

        // Map user to staff first
        $staff = $user->staff;
        if (!$staff) {
            return back()->with('error', 'Staff record not found for user');
        }

        // Calculate total days
        // Normalize both dates to start of day to avoid time component issues
        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->startOfDay();
        // diffInDays returns the number of days between dates (exclusive)
        // For same date: diffInDays = 0, +1 = 1 day (correct)
        // For consecutive dates: diffInDays = 1, +1 = 2 days (correct)
        // Use abs() to ensure positive value and ensure endDate >= startDate
        $totalDays = abs($endDate->diffInDays($startDate)) + 1;

        // Get OT hours for replacement leave
        $otHours = 0;
        // Determine if selected type is replacement
        $selectedLeaveType = \App\Models\LeaveType::find($validated['leave_type_id']);
        if ($selectedLeaveType && strtolower($selectedLeaveType->type_name) === 'replacement') {
            $otClaims = collect();
            $overtimeIds = \App\Models\Overtime::where('staff_id', $staff->id)->pluck('id')->toArray();
            if (!empty($overtimeIds)) {
                $allPayrollClaims = OTClaim::approved()->get();
                
                $otClaims = $allPayrollClaims->filter(function($claim) use ($overtimeIds) {
                    $claimOtIds = $claim->ot_ids ?? [];
                    if (is_string($claimOtIds)) {
                        $claimOtIds = json_decode($claimOtIds, true) ?? [];
                    }
                    return !empty(array_intersect($overtimeIds, $claimOtIds));
                });
            }
            $otHours = $otClaims->sum(function($claim) {
                return ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
            });
        }

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        $leave = Leave::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'attachment' => $attachmentPath,
        ]);

        // The auto-approval happens in the model's booted method automatically
        // Refresh to get the updated status and rejection_reason
        $leave->refresh();

        if ($leave->status === 'rejected') {
            return redirect()->route('staff.leave-status')
                ->with('error', 'Leave request was rejected: ' . ($leave->rejection_reason ?? 'Unknown reason'));
        }

        return redirect()->route('staff.leave-status')->with('success', "Leave request submitted! Status: " . ucfirst($leave->status));
    }

    public function staffLeaveStatus()
    {
        // Get all staff with their active leaves
        $staffLeaves = Leave::with('staff.user', 'leaveType')
            ->where('status', 'approved')
            ->orWhere('status', 'pending')
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return view('admin.staffLeaveStatus', compact('staffLeaves'));
    }

    /**
     * Download or view leave attachment
     */
    public function downloadAttachment(Leave $leave)
    {
        $user = Auth::user();
        
        // Eager load relationships
        $leave->load('staff.user');
        
        // Check if attachment exists
        if (!$leave->attachment) {
            abort(404, 'Attachment not found');
        }
        
        // Check permissions
        // Admin can view any attachment, staff can only view their own
        if ($user->isAdmin()) {
            // Admin can view any attachment
        } elseif ($user->isStaff()) {
            // Staff can only view their own attachments
            if (!$leave->staff || $leave->staff->user_id !== $user->id) {
                abort(403, 'You do not have permission to view this attachment');
            }
        } else {
            abort(403, 'Unauthorized');
        }
        
        // Check if file exists
        if (!Storage::disk('public')->exists($leave->attachment)) {
            abort(404, 'File not found');
        }
        
        // Get file path
        $filePath = Storage::disk('public')->path($leave->attachment);
        $fileName = basename($leave->attachment);
        
        // Return file response (for viewing in browser)
        return response()->file($filePath, [
            'Content-Type' => Storage::disk('public')->mimeType($leave->attachment),
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }

    /**
     * Helper: Calculate leave balance for a user
     */
    private function getLeaveBalance($staffId, $leaveType)
    {
        // $leaveType can be a type_name string (legacy callers) or an id
        $maxDays = 0;
        $typeRecord = null;
        if (is_int($leaveType) || ctype_digit((string)$leaveType)) {
            $typeRecord = \App\Models\LeaveType::find($leaveType);
        } else {
            $typeRecord = \App\Models\LeaveType::where('type_name', $leaveType)->first();
        }

        if ($typeRecord) {
            $maxDays = $typeRecord->max_days ?? (Leave::$maxLeaves[$typeRecord->type_name] ?? 0);
            $takenDays = Leave::where('staff_id', $staffId)
                ->where('leave_type_id', $typeRecord->id)
                ->where('status', 'approved')
                ->sum('total_days');
        } else {
            // fallback: no matching LeaveType record found; rely on predefined maxes and assume no taken days
            $maxDays = Leave::$maxLeaves[$leaveType] ?? 0;
            $takenDays = 0;
        }

        return [
            'max' => $maxDays,
            'taken' => $takenDays,
            'balance' => $maxDays - $takenDays,
        ];
    }

    /**
     * Helper: Calculate replacement leave balance
     */
    private function getReplacementLeaveBalance($staffId)
    {
        // Get the user from staff to query OT claims
        $staff = \App\Models\Staff::find($staffId);
        if (!$staff || !$staff->user) {
            return ['max' => 0, 'taken' => 0, 'balance' => 0];
        }

        // Get OT claims via ot_ids matching
        $overtimeIds = \App\Models\Overtime::where('staff_id', $staffId)->pluck('id')->toArray();
        $allOtClaims = OTClaim::approved()->get();
        $otClaims = $allOtClaims->filter(function($claim) use ($overtimeIds) {
                $claimOtIds = $claim->ot_ids ?? [];
                if (is_string($claimOtIds)) {
                    $claimOtIds = json_decode($claimOtIds, true) ?? [];
                }
                return !empty(array_intersect($overtimeIds, $claimOtIds));
            })
            ->approved()
            ->get();

        $totalOTHours = $otClaims->sum(function($claim) {
            return ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
        });

        $maxDays = floor($totalOTHours / 8);

        $replacementType = \App\Models\LeaveType::whereRaw('LOWER(type_name) = ?', [strtolower('replacement')])->first();
        if ($replacementType) {
            $takenDays = Leave::where('staff_id', $staffId)
                ->where('leave_type_id', $replacementType->id)
                ->where('status', 'approved')
                ->sum('total_days');
        } else {
            // No replacement leave type defined in DB; assume none taken
            $takenDays = 0;
        }

        return [
            'max' => $maxDays,
            'taken' => $takenDays,
            'balance' => $maxDays - $takenDays,
        ];
    }
}
