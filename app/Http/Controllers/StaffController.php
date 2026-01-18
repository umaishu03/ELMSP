<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Staff;
// Admin model removed - admin table dropped
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    /**
     * Download CSV list of current staff members
     */
    public function downloadTemplate()
    {
        // Get all users with their staff/admin records, excluding seeded test users
        $excludedEmails = ['admin@gmail.com', 'staff@gmail.com'];
        $users = User::with(['staff'])
            ->whereNotIn('email', $excludedEmails)
            ->orderBy('name')
            ->get();
        
        // Create CSV content with proper Excel formatting
        $csvData = [];
        
        // Add header row (fixed typo: "department" not "departmer")
        $csvData[] = ['staff_name', 'email', 'id', 'department', 'role'];
        
        // Add data rows
        foreach ($users as $user) {
            $department = '';
            if ($user->staff) {
                $department = $user->staff->department;
            }
            // Admin users don't have department (admin table removed)
            
            // Ensure all fields are properly formatted
            $csvData[] = [
                trim($user->name ?? ''),
                trim($user->email ?? ''),
                trim($user->employee_id ?? ''),
                trim($department),
                trim($user->role ?? '')
            ];
        }
        
        // Generate CSV content with proper Excel compatibility
        $csvContent = '';
        foreach ($csvData as $row) {
            $escapedRow = array_map(function($field) {
                // Always quote fields to ensure Excel compatibility
                $field = (string) $field;
                // Escape quotes by doubling them
                $field = str_replace('"', '""', $field);
                // Wrap in quotes
                return '"' . $field . '"';
            }, $row);
            
            $csvContent .= implode(',', $escapedRow) . "\r\n"; // Use \r\n for Windows Excel compatibility
        }
        
        // Add BOM for proper UTF-8 encoding in Excel
        $bom = "\xEF\xBB\xBF";
        $csvContent = $bom . $csvContent;
        
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="staff_list_' . date('Y-m-d_H-i-s') . '.csv"',
            'Cache-Control' => 'no-cache, must-revalidate',
            'Pragma' => 'no-cache',
        ];
        
        return response($csvContent, 200, $headers);
    }
    
    /**
     * Show staff management page
     */
    public function index()
    {
        // Get all users with their staff records, excluding seeded test users
        $excludedEmails = ['admin@gmail.com', 'staff@gmail.com'];
        $allUsers = User::with(['staff'])
            ->leftJoin('staff', 'users.id', '=', 'staff.user_id')
            ->whereNotIn('users.email', $excludedEmails)
            ->select('users.*')
            ->orderByRaw('COALESCE(staff.employee_id, "") ASC')
            ->orderBy('users.name', 'asc') // Secondary sort by name for users without employee_id
            ->get();
        
        // Get latest 5 users
        $latestUsers = $allUsers->take(5);
        
        return view('admin.manageStaff', [
            'users' => $allUsers,
            'latestUsers' => $latestUsers
        ]);
    }
    
    /**
     * Get department color class
     */
    public static function getDepartmentColor($department)
    {
        $colors = [
            'manager' => 'bg-purple-100 text-purple-800',
            'supervisor' => 'bg-blue-100 text-blue-800',
            'cashier' => 'bg-yellow-100 text-yellow-800',
            'barista' => 'bg-orange-100 text-orange-800',
            'joki' => 'bg-cyan-100 text-cyan-800',
            'waiter' => 'bg-green-100 text-green-800',
            'kitchen' => 'bg-red-100 text-red-800',
        ];
        
        return $colors[strtolower($department)] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Show staff details (for AJAX)
     */
    public function show(User $user)
    {
        try {
            $user->load(['staff']);
            
            return response()->json([
                'success' => true,
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load staff details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update staff member
     */
    public function update(Request $request, User $user)
    {
        try {
            DB::beginTransaction();
            
            // Validate request
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email,' . $user->id,
                'employee_id' => 'required|string|max:255',
                'department' => 'required|string|max:255',
                'status' => 'required|string|in:active,inactive',
                'role' => 'required|string|in:admin,staff',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'hire_date' => 'nullable|date',
                'appointment_date' => 'nullable|date',
                'salary' => 'nullable|numeric|min:0',
                'admin_level' => 'nullable|string|max:255',
            ]);
            
            // Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'role' => $validated['role'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ]);
            
            // Update staff or admin record
            if ($validated['role'] === 'staff') {
                $newDepartment = $validated['department'];
                $oldDepartment = $user->staff?->department;
                $newStatus = $validated['status'];
                $oldStatus = $user->staff?->status;
                
                // Check department limit only if status is 'active' and:
                // 1. Creating new staff record, OR
                // 2. Changing department, OR
                // 3. Activating inactive staff
                if ($newStatus === 'active' && 
                    (!$user->staff || $newDepartment !== $oldDepartment || $oldStatus === 'inactive')) {
                    
                    $excludeStaffId = $user->staff?->id;
                    $limitCheck = Staff::checkDepartmentLimit($newDepartment, $excludeStaffId);
                    
                    if ($limitCheck['reached']) {
                        throw new \Exception($limitCheck['message']);
                    }
                }
                
                if ($user->staff) {
                    $user->staff->update([
                        'employee_id' => $validated['employee_id'],
                        'department' => $validated['department'],
                        'status' => $validated['status'],
                        'hire_date' => $validated['hire_date'] ?? null,
                        'salary' => $validated['salary'] ?? null,
                    ]);
                } else {
                    // Create staff record if it doesn't exist
                    Staff::create([
                        'user_id' => $user->id,
                        'employee_id' => $validated['employee_id'],
                        'department' => $validated['department'],
                        'status' => $validated['status'],
                        'hire_date' => $validated['hire_date'] ?? null,
                        'salary' => $validated['salary'] ?? null,
                    ]);
                }
                
                // Admin table removed - no admin record to delete
            } else {
                // Admin role - admin table removed, only update user role
                // Delete staff record if exists (user switching from staff to admin)
                if ($user->staff) {
                    $user->staff->delete();
                }
                // Admin users are identified by users.role = 'admin' only
            }
            
            DB::commit();
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Staff updated successfully'
                ]);
            }
            
            return redirect()->route('admin.manage-staff')
                ->with('success', "Staff member '{$user->name}' has been updated successfully.");
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => $e->getMessage(),
                    'errors' => $e->errors()
                ], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Failed to update staff: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('admin.manage-staff')
                ->with('error', 'Failed to update staff member: ' . $e->getMessage());
        }
    }

    /**
     * Delete a staff member
     */
    public function destroy(User $user)
    {
        try {
            // Check if user exists and is not a seeded user
            $excludedEmails = ['admin@gmail.com', 'staff@gmail.com'];
            if (in_array($user->email, $excludedEmails)) {
                return redirect()->route('admin.manage-staff')
                    ->with('error', 'Cannot delete seeded test users.');
            }

            // Delete related records first (due to foreign key constraints)
            if ($user->staff) {
                $user->staff->delete();
            }
            // Admin table removed - admin users are identified by users.role only

            // Delete the user
            $user->delete();

            return redirect()->route('admin.manage-staff')
                ->with('success', "Staff member '{$user->name}' has been deleted successfully.");
                
        } catch (\Exception $e) {
            return redirect()->route('admin.manage-staff')
                ->with('error', 'Failed to delete staff member: ' . $e->getMessage());
        }
    }
}