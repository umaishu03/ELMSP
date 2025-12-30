<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overtime;
use App\Models\Shift;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OvertimeApprovalController extends Controller
{
    // List pending overtimes
    public function index()
    {
        $pending = Overtime::where('status', 'pending')
            ->with('staff.user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.overtimes.pending', compact('pending'));
    }

    // Approve an overtime
    public function approve(Request $request, Overtime $overtime)
    {
        // Validate based on business rules
        $validation = Overtime::validateOT($overtime);

        if (! $validation['valid']) {
            return redirect()->back()->with('error', $validation['message']);
        }

        $overtime->status = 'approved';
        $overtime->remarks = $request->input('remarks') ?? ('Approved by admin ' . Auth::id());
        $overtime->save();

        // Load staff relationship
        $overtime->load('staff');
        
        // Create or update shift to show 12 hours work for that day
        if ($overtime->staff) {
            $otDate = Carbon::parse($overtime->ot_date)->format('Y-m-d');
            
            // Check if shift already exists for this staff on this date
            $existingShift = Shift::where('staff_id', $overtime->staff_id)
                ->where('date', $otDate)
                ->first();
            
            // Calculate times for 12 hours work + 1 hour break = 13 hours total
            // Default: 9:00 AM to 10:00 PM (13 hours total, 12h work + 1h break)
            $startTime = '09:00';
            $endTime = '22:00';
            $breakMinutes = 60;
            
            // If shift exists, update it; otherwise create new one
            if ($existingShift) {
                // Update existing shift to show 12 hours work
                $existingShift->update([
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'break_minutes' => $breakMinutes,
                    'rest_day' => false,
                ]);
            } else {
                // Create new shift showing 12 hours work
                Shift::create([
                    'staff_id' => $overtime->staff_id,
                    'date' => $otDate,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'break_minutes' => $breakMinutes,
                    'rest_day' => false,
                ]);
            }
        }

        // Calculate the Monday (start of week) for the overtime date
        // This ensures the timetable shows the week containing the approved overtime
        $otDateCarbon = Carbon::parse($overtime->ot_date);
        $weekStart = $otDateCarbon->copy()->startOfWeek(); // Monday of that week
        
        // Redirect to timetable with the week_start parameter
        return redirect()->route('admin.staff-timetable', [
            'week_start' => $weekStart->format('Y-m-d')
        ])->with('success', 'Overtime approved. Timetable updated to show 12 hours work for that day.');
    }

    // Reject an overtime
    public function reject(Request $request, Overtime $overtime)
    {
        $overtime->status = 'rejected';
        $overtime->remarks = $request->input('remarks') ?? ('Rejected by admin ' . Auth::id());
        $overtime->save();

        return redirect()->back()->with('success', 'Overtime rejected');
    }
}
