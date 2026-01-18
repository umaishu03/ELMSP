# ELMSP Leave Management System - Complete Workflow

## System Overview

The leave management system in ELMSP consists of three main operations:
1. **Initialization** - Setting up initial leave balances for staff
2. **Application** - Processing leave requests
3. **Balance Update** - Tracking leave usage

---

## Part 1: Initialization - Setting Up Initial Leave Balances

### When Does This Happen?
When staff members are first created, or during database seeding.

### How It Works
**File**: [database/seeders/LeaveBalancesSeeder.php](database/seeders/LeaveBalancesSeeder.php)

1. System iterates through all staff members
2. For each leave type (annual, medical, unpaid, etc.), creates a `LeaveBalance` record with:
   - **total_days** = LeaveType.max_days
     - For Unpaid: 10 days
     - For Annual: 14 days
     - For Medical: 14 days
     - For Hospitalization: 30 days
     - For Emergency: 7 days
     - For Marriage: 3 days
     - For Replacement: Calculated from OT claims
   
   - **used_days** = Sum of all approved leaves of that type (initially 0)
   - **remaining_days** = total_days - used_days (initially equals total_days)

### Database Record After Initialization
For a new staff member on Unpaid Leave type:
```
| staff_id | leave_type_id | total_days | used_days | remaining_days |
|----------|---------------|-----------|-----------|-----------------|
| 1        | 7 (unpaid)    | 10.0      | 0.0       | 10.0            |
```

---

## Part 2: Leave Application - Automatic Approval/Rejection

### When Does This Happen?
When staff submits a leave request via the "Apply Leave" form.

### The Process

**File**: [app/Models/Leave.php](app/Models/Leave.php) - created() event hook

#### Step 1: Create Leave Record
```
POST /leave/apply
→ Creates Leave record with status = 'pending'
```

#### Step 2: Auto-Approval Logic (8 Validation Checks)
The `created()` event automatically triggers `autoApproveOrReject()` which checks:

| Check | Rule | Exempt Types |
|-------|------|--------------|
| 1 | **Sufficient balance** - Must have enough days available | None |
| 2 | **Advance notice** - Must apply 3+ days before leave date | Emergency, Medical, Hospitalization |
| 3 | **Weekend restriction** - Can't take leave on weekends | Emergency, Medical, Hospitalization |
| 4 | **Medical certificate** - Required for Medical/Hospitalization | - |
| 5 | **Weekly entitlement** - Max 2 days per week | Emergency, Medical, Hospitalization |
| 6 | **Department daily quota** - Max X people per day by department | Emergency, Medical, Hospitalization |
| 7 | **Department weekly quota** - Max X people per week | Emergency, Medical, Hospitalization |
| 8 | **Overtime conflict** - Can't take leave on scheduled OT days | Emergency, Medical, Hospitalization |

#### Step 3: Result
**If ALL checks pass:**
- Leave status → `'approved'`
- auto_approved → `true`
- approved_at → Current timestamp

**If ANY check fails:**
- Leave status → `'rejected'`
- auto_approved → `false`
- rejection_reason → Specific message explaining why

#### Step 4: If Auto-Approved, Apply to Balance Immediately
Code triggers:
```php
if ($originalStatus !== 'approved' && $leave->status === 'approved') {
    $leave->applyToBalance();
    $leave->linkShifts();
}
```

---

## Part 3: Balance Update - When Leave is Approved

### When Does This Happen?
When a leave becomes approved (either auto-approved on creation, or manually approved by admin later).

### How It Works

**File**: [app/Models/Leave.php](app/Models/Leave.php) - `applyToBalance()` method

#### If LeaveBalance Row Doesn't Exist Yet
Falls back to creating it:
```php
$total = $type?->max_days ?? (self::$maxLeaves[$type?->type_name ?? ''] ?? 0);
$lb = LeaveBalance::create([
    'staff_id' => $staff_id,
    'leave_type_id' => $leave_type_id,
    'total_days' => $total,
    'used_days' => 0,
    'remaining_days' => $total,
]);
```

#### Update the Balance
```php
$oldUsed = (float) $lb->used_days;
$oldRemaining = (float) $lb->remaining_days;
$daysToApply = (float) $this->total_days;

$used = $oldUsed + $daysToApply;
$remaining = $oldRemaining - $daysToApply;

$lb->used_days = $used;
$lb->remaining_days = $remaining;
$lb->save();
```

### Example: Staff Takes 1-Day Unpaid Leave

**Before:**
```
used_days = 0, remaining_days = 10, total_days = 10
```

**Leave applied with total_days = 1:**
```
used_days = 0 + 1 = 1
remaining_days = 10 - 1 = 9
total_days = 10 (unchanged)
```

**After:**
```
used_days = 1, remaining_days = 9, total_days = 10
```

---

## Part 4: Staff Dashboard - Displaying Leave Balance

### Files Involved
- **Backend**: [app/Http/Controllers/LeaveController.php](app/Http/Controllers/LeaveController.php) - `getLeaveBalance()` method
- **View**: [resources/views/dashboard/staff.blade.php](resources/views/dashboard/staff.blade.php) - Lines 90-165

### Data Preparation Strategy
The dashboard uses a **hybrid approach** to ensure data accuracy:

1. **Query Leave records** - Get all approved leaves grouped by type
2. **Query LeaveBalance** - Get the stored balance data
3. **Merge & validate** - Use LeaveBalance as primary source, fallback to Leave records

### Logic Flow
```php
For each Leave Type with approved leaves:
    IF LeaveBalance exists:
        USE: LeaveBalance.total_days, used_days, remaining_days
        VALIDATE: If total looks wrong, recalculate from used + remaining
    ELSE:
        USE: Calculate from Leave records
        Calculate used_days = sum of approved leave days
        Set remaining_days = 0 (conservative estimate)

Also include LeaveBalance entries with NO Leave records yet
    (These show the full allocation before any leaves are used)
```

### Chart Display
The Leave Balance chart displays stacked horizontal bars:
- **Green bar** = remaining_days (unpaid: 9)
- **Red bar** = used_days (unpaid: 1)
- **Total length** = total_days (unpaid: 10)

---

## Why Your Chart Shows "remaining: 0, total: 1"

### Root Causes (In Order of Likelihood)

#### Cause 1: LeaveBalance Created with Wrong total_days
When `applyToBalance()` runs and no LeaveBalance exists, it tries to get the max_days:
```php
$total = $type?->max_days ?? (self::$maxLeaves[$type?->type_name ?? ''] ?? 0);
```

**Problem**: If LeaveType record is NULL or corrupted:
- `$type` = null
- `$type?->max_days` = null
- Falls back to `self::$maxLeaves[$type?->type_name ?? '']` 
- But `$type` is null, so `$type?->type_name` = null
- `$maxLeaves[null]` = undefined index
- Final fallback: 0
- Creates LeaveBalance with total_days = 0

Then when leave (1 day) is applied:
- used_days = 0 + 1 = 1
- remaining_days = 0 - 1 = -1 → max(0, -1) = 0

**Result**: total: 1, used: 1, remaining: 0 ✗

#### Cause 2: Seeder Wasn't Run
If LeaveBalancesSeeder never ran:
- No initial LeaveBalance records exist
- First leave application triggers `applyToBalance()` 
- Fallback creates LeaveBalance as above (wrong total)

#### Cause 3: Manual Database Corruption
LeaveBalance record manually created or updated with incorrect total_days value.

---

## How to Fix This

### Option 1: Re-run the Seeder (Recommended)
```bash
php artisan db:seed --class=LeaveBalancesSeeder
```

This will:
- Recalculate all LeaveBalance records
- Set total_days correctly from LeaveType.max_days
- Recalculate used_days from approved Leave records
- Set remaining_days = total_days - used_days

### Option 2: Fix the applyToBalance() Method
Add validation to prevent null LeaveType:

```php
public function applyToBalance()
{
    if (! $this->staff_id || ! $this->leave_type_id || ! $this->total_days) {
        Log::warning('Leave applyToBalance skipped - missing required fields');
        return;
    }

    // FIX: Validate LeaveType exists
    $leaveType = $this->leaveType;
    if (!$leaveType) {
        Log::error('LeaveType not found', ['leave_type_id' => $this->leave_type_id]);
        return; // Abort instead of creating wrong balance
    }

    // Rest of method...
}
```

### Option 3: Manual Database Fix (After Seeding)
Once seeded, the LeaveBalance should show:
```sql
SELECT * FROM leave_balances WHERE leave_type_id = 7; 
-- Should show: total_days = 10, used_days = 1, remaining_days = 9
```

If still wrong, check:
```sql
SELECT id, type_name, max_days FROM leave_types WHERE type_name = 'unpaid';
-- Should show: max_days = 10
```

---

## System Diagram

```
Staff Created
    ↓
LeaveBalancesSeeder runs
    ↓
LeaveBalance created for each type
│  unpaid: total=10, used=0, remaining=10
│  annual: total=14, used=0, remaining=14
│  medical: total=14, used=0, remaining=14
│
↓
Staff applies for 1-day Unpaid Leave
    ↓
Leave created with status='pending'
    ↓
created() event fires
    ↓
autoApproveOrReject() checks 8 rules
    ↓
Status changes to 'approved'
    ↓
applyToBalance() runs
    ↓
LeaveBalance updated
│  unpaid: total=10, used=1, remaining=9
│
↓
Dashboard displays:
│  Green bar: 9 remaining days
│  Red bar: 1 used day
│  Total: 10 days
```

---

## Key Takeaway

The system is **event-driven and automatic**:
1. ✅ Leave submission is auto-approved/rejected instantly (8 rules)
2. ✅ If approved, balance updates immediately via model events
3. ✅ Dashboard queries latest balance and displays chart
4. ✅ All operations logged for audit trail

The chart not showing correct data usually means **LeaveBalance has wrong values**, which typically happens when the seeder wasn't run or LeaveType is missing.
