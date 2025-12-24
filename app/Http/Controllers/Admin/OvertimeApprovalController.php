<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Overtime;
use Illuminate\Support\Facades\Auth;

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

        return redirect()->back()->with('success', 'Overtime approved');
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
