<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use League\Csv\Reader;
use League\Csv\Statement;
use App\Models\User;
use App\Models\Staff;
// Admin model removed - admin table dropped
use App\Mail\UserCredentials;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class StaffRegisterController extends Controller
{
    /**
     * Store uploaded CSV file and process staff registration
     */
    public function store(Request $request)
    {
        // Step 4: Controller Validation
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ], [
            'csv_file.required' => 'Please select a CSV file to upload.',
            'csv_file.file' => 'The uploaded file must be a valid file.',
            'csv_file.mimes' => 'The file must be a CSV or TXT file.',
            'csv_file.max' => 'The file size must not exceed 2MB.'
        ]);
        
        try {
            // Step 5: CSV Reading
            $file = $request->file('csv_file');
            $csv = Reader::createFromPath($file->getPathname(), 'r');
            
            // Set header offset to skip the first row (headers)
            $csv->setHeaderOffset(0);
            
            // Get the header record
            $header = $csv->getHeader();
            
            // Validate required headers
            $requiredHeaders = ['staff_name', 'email', 'id', 'department', 'role'];
            $missingHeaders = array_diff($requiredHeaders, $header);
            
            if (!empty($missingHeaders)) {
                return redirect()->route('admin.manage-staff')
                    ->with('error', 'CSV file is missing required headers: ' . implode(', ', $missingHeaders));
            }
            
            // Create statement to iterate over records
            $stmt = Statement::create();
            $records = $stmt->process($csv);
            
            $processedCount = 0;
            $createdCount = 0;
            $errors = [];
            $createdUsers = [];
            
            // Iterate over each record
            foreach ($records as $offset => $record) {
                $processedCount++;
                
                // Validate each record
                $recordErrors = $this->validateRecord($record, $offset + 2); // +2 because header is row 1, and offset is 0-based
                
                if (!empty($recordErrors)) {
                    $errors = array_merge($errors, $recordErrors);
                    continue; // Skip this record if validation fails
                }
                
                // Step 6: User Creation
                try {
                    $user = $this->createUser($record);
                    $createdUsers[] = $user;
                    $createdCount++;
                } catch (\Exception $e) {
                    // Only add error if it's not already in the errors array (avoid duplicates)
                    $errorMessage = "Row " . ($offset + 2) . ": Failed to create user - " . $e->getMessage();
                    if (!in_array($errorMessage, $errors)) {
                        $errors[] = $errorMessage;
                    }
                }
            }
            
            $failedCount = count($errors);

            // Always build a summary including both success and failure counts
            $summary = "Import finished. Successful: {$createdCount}. Failed: {$failedCount}.";

            // Build success details list
            $successMessage = $summary;
            if ($createdCount > 0) {
                $successMessage .= "<br><br>Created users:<br>" . implode('<br>', array_map(function($user) {
                    $emailStatus = isset($user->email_sent) && $user->email_sent ?
                        '<span style="color: green;">✓ Email sent</span>' :
                        '<span style="color: red;">✗ Email failed</span>';
                    return "• {$user->name} ({$user->email}) - {$user->role} - {$emailStatus}";
                }, $createdUsers));
            }

            $redirect = redirect()->route('admin.manage-staff')->with('success', $successMessage);

            // Include detailed error list (row + message) when any failures occurred
            // Remove duplicates before displaying
            if ($failedCount > 0) {
                $uniqueErrors = array_unique($errors);
                $errorDetails = implode('<br>', $uniqueErrors);
                $redirect = $redirect->with('error', $errorDetails);
            }

            return $redirect;
                
        } catch (\Exception $e) {
            return redirect()->route('admin.manage-staff')
                ->with('error', 'Error processing CSV file: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate individual CSV record
     */
    private function validateRecord($record, $rowNumber)
    {
        $errors = [];
        
        // Check required fields
        if (empty($record['staff_name'])) {
            $errors[] = "Row {$rowNumber}: Staff name is required";
        }
        
        if (empty($record['email'])) {
            $errors[] = "Row {$rowNumber}: Email is required";
        } elseif (!filter_var($record['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Row {$rowNumber}: Invalid email format";
        }
        
        if (empty($record['id'])) {
            $errors[] = "Row {$rowNumber}: Employee ID is required";
        } elseif (!preg_match('/^YS\d{3}$/', $record['id'])) {
            $errors[] = "Row {$rowNumber}: Employee ID must be in format YS399, YS400, etc.";
        }
        
        if (empty($record['department'])) {
            $errors[] = "Row {$rowNumber}: Department is required";
        } else {
            $validDepartments = ['manager', 'supervisor', 'cashier', 'barista', 'joki', 'waiter', 'kitchen'];
            $department = strtolower(trim($record['department']));
            if (!in_array($department, $validDepartments)) {
                $errors[] = "Row {$rowNumber}: Invalid department '{$record['department']}'. Must be one of: " . implode(', ', $validDepartments);
            } else {
                // Check department limit FIRST before other validations
                $limitCheck = Staff::checkDepartmentLimit($department);
                if ($limitCheck['reached']) {
                    $errors[] = "Row {$rowNumber}: {$limitCheck['message']}";
                }
            }
        }
        
        if (empty($record['role'])) {
            $errors[] = "Row {$rowNumber}: Role is required";
        } else {
            $validRoles = ['staff', 'admin'];
            if (!in_array(strtolower($record['role']), $validRoles)) {
                $errors[] = "Row {$rowNumber}: Invalid role. Must be 'staff' or 'admin'";
            }
        }
        
        return $errors;
    }
    
    /**
     * Create a new user from CSV record
     */
    private function createUser($record)
    {
        // Generate random 10-character password
        $plainPassword = Str::random(10);
        
        // Check if user already exists
        $existingUser = User::where('email', $record['email'])->first();
        if ($existingUser) {
            throw new \Exception("User with email {$record['email']} already exists");
        }
        
        // Check if employee ID already exists in staff table
        $existingStaff = Staff::where('employee_id', $record['id'])->first();
        if ($existingStaff) {
            throw new \Exception("Employee ID {$record['id']} already exists");
        }
        
        $role = strtolower(trim($record['role']));
        $department = strtolower(trim($record['department']));
        $employeeId = trim($record['id']);
        
        // Create new user
        $user = User::create([
            'name' => trim($record['staff_name']),
            'email' => trim($record['email']),
            'password' => Hash::make($plainPassword),
            'role' => $role,
            'phone' => null, // Not provided in CSV
            'address' => null, // Not provided in CSV
            'first_login' => true,
        ]);
        
        // Step 7: Role-Specific Record Creation
        // Note: Admin users don't need separate records - role is stored in users.role
        if ($role === 'staff') {
            $this->createStaffRecord($user, $employeeId, $department);
        }
        // Admin role users are identified by users.role = 'admin' only
        
        // Step 8: Send Email with Credentials
        try {
            Mail::to($user->email)->send(new UserCredentials($user, $plainPassword));
            $user->email_sent = true;
        } catch (\Exception $e) {
            $user->email_sent = false;
            $user->email_error = $e->getMessage();
        }
        
        // Store the generated password for display
        $user->generated_password = $plainPassword;
        
        return $user;
    }
    
    /**
     * Create staff record
     */
    private function createStaffRecord($user, $employeeId, $department)
    {
        // Note: Department limit is already checked in validateRecord()
        // This is just a safety check in case validation was bypassed
        $limitCheck = Staff::checkDepartmentLimit($department);
        if ($limitCheck['reached']) {
            throw new \Exception($limitCheck['message']);
        }

        Staff::create([
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'department' => $department,
            'hire_date' => now(),
            'salary' => $this->getDefaultSalary($department),
            'status' => 'active',
        ]);
    }
    
    // Admin record creation removed - admin table dropped
    // Admin users are identified by users.role = 'admin' only
    
    /**
     * Get default salary based on department
     */
    private function getDefaultSalary($department)
    {
        $salaries = [
            'manager' => 4000.00,
            'supervisor' => 3000.00,
            'cashier' => 2000.00,
            'barista' => 1800.00,
            'joki' => 1600.00,
            'waiter' => 1500.00,
            'kitchen' => 1800.00,
        ];
        
        return $salaries[$department] ?? 1500.00;
    }
    
    /**
     * Get default admin permissions based on department
     */
    private function getDefaultAdminPermissions($department)
    {
        $basePermissions = ['view_dashboard', 'manage_users', 'view_reports'];
        
        $departmentPermissions = [
            'manager' => ['manage_staff', 'manage_payroll', 'approve_requests'],
            'supervisor' => ['manage_staff', 'view_payroll'],
            'cashier' => ['manage_transactions', 'view_sales'],
            'barista' => ['manage_inventory', 'view_sales'],
            'joki' => ['manage_orders', 'view_sales'],
            'waiter' => ['manage_orders', 'view_sales'],
            'kitchen' => ['manage_inventory', 'manage_orders'],
        ];
        
        $permissions = array_merge($basePermissions, $departmentPermissions[$department] ?? []);
        
        return $permissions;
    }
}