<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Http\Response;

class StaffController extends Controller
{
    /**
     * Download CSV list of current staff members
     */
    public function downloadTemplate()
    {
        // Get all users with their staff/admin records, excluding seeded test users
        $excludedEmails = ['admin@gmail.com', 'staff@gmail.com'];
        $users = User::with(['staff', 'admin'])
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
            } elseif ($user->admin) {
                $department = $user->admin->department;
            }
            
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
        // Get all users with their staff/admin records, excluding seeded test users
        $excludedEmails = ['admin@gmail.com', 'staff@gmail.com'];
        $users = User::with(['staff', 'admin'])
            ->whereNotIn('email', $excludedEmails)
            ->get();
        
        return view('admin.manageStaff', compact('users'));
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
            if ($user->admin) {
                $user->admin->delete();
            }

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