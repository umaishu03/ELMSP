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

        // Get user's approved OT hours for replacement leave calculation via payroll relation
        $approvedOTClaims = OTClaim::whereHas('payroll', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('claim_type', 'payroll')
            ->approved()
            ->get();

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
                // For unpaid leave, ensure max is 10 (even if balance record has 0)
                if ($typeName === 'unpaid') {
                    $max = 10;
                    $taken = (float)$b->used_days;
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => $taken,
                        'balance' => $balance,
                    ];
                } else {
                $leaveBalance[$typeName] = [
                    'max' => (float)$b->total_days,
                    'taken' => (float)$b->used_days,
                    'balance' => (float)$b->remaining_days,
                ];
                }
            } else {
                $leaveBalance[$typeName] = $computeFallback($typeName);
            }
        }

        $leaveTypes = \App\Models\LeaveType::orderBy('type_name')->get();
        return view('staff.leave.application', compact('leaveBalance', 'totalOTHours', 'leaveTypes'));
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

        // compute OT hours for replacement fallback via payroll relation
        $approvedOTClaims = OTClaim::whereHas('payroll', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->where('claim_type', 'payroll')
            ->approved()
            ->get();
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
                // For unpaid leave, ensure max is 10 (even if balance record has 0)
                if ($typeName === 'unpaid') {
                    $max = 10;
                    $taken = (float)$b->used_days;
                    $balance = max(0, $max - $taken);
                    $leaveBalance[$typeName] = [
                        'max' => $max,
                        'taken' => $taken,
                        'balance' => $balance,
                    ];
                } else {
                $leaveBalance[$typeName] = [
                    'max' => (float)$b->total_days,
                    'taken' => (float)$b->used_days,
                    'balance' => (float)$b->remaining_days,
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

        return view('staff.leave.status', compact('leaveApplications', 'leaveBalance', 'stats'));
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

        // Calculate total days
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $endDate->diffInDays($startDate) + 1;

        // Get OT hours for replacement leave
        $otHours = 0;
        // Determine if selected type is replacement
        $selectedLeaveType = \App\Models\LeaveType::find($validated['leave_type_id']);
        if ($selectedLeaveType && strtolower($selectedLeaveType->type_name) === 'replacement') {
            $otClaims = OTClaim::whereHas('payroll', function($q) use ($user) {
                    $q->where('user_id', $user->id);
                })
                ->approved()
                ->get();
            $otHours = $otClaims->sum(function($claim) {
                return ($claim->fulltime_hours ?? 0) + ($claim->public_holiday_hours ?? 0);
            });
        }

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        // Map user to staff and create leave record
        $staff = $user->staff;
        if (!$staff) {
            return back()->with('error', 'Staff record not found for user');
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

        return redirect()->route('staff.leave-status')->with('success', "Leave request submitted! Status: " . ucfirst($leave->status));
    }

    public function staffLeaveStatus()
    {
        // Get all staff with their active leaves
        $staffLeaves = Leave::with('staff.user', 'leaveType')
            ->where('status', 'approved')
            ->orWhere('status', 'pending')
            ->orderBy('created_at', 'desc')
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

        $otClaims = OTClaim::whereHas('payroll', function($q) use ($staff) {
                $q->where('user_id', $staff->user->id);
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
