# ELMSP System - Leave, Overtime & Payroll Database Schema

## Overview
This document describes the new database tables and relationships for the automated leave approval, overtime management, and payroll calculation system.

---

## 1. OVERTIMES TABLE
**Purpose:** Tracks all overtime applications submitted by staff

### Columns:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `ot_type` - Enum: 'fulltime' or 'public_holiday'
- `ot_date` - Date of the overtime work
- `hours` - Number of hours worked (decimal)
- `status` - Enum: 'pending', 'approved', 'rejected'
- `remarks` - Admin notes/reasons
- `created_at`, `updated_at` - Timestamps

### Business Logic:
- **Automatic Approval:** OT requires admin approval and is validated against department limits
- **Department OT Application Limits per week:**
  - Manager: 1 OT application per week
  - Supervisor: 1 OT application per week
  - Cashier: 2 OT applications per week
  - Barista: 2 OT applications per week
  - Joki: 2 OT applications per week
  - Waiter: 3 OT applications per week
  - Kitchen: 3 OT applications per week

- **Maximum OT applications per person per week:** 2 applications

- **Maximum OT hours per person per day:**
  - Manager/Supervisor: 2 hours/day (12 hours workday)
  - Others: 4 hours/day (7.5 hours workday)

---

## 2. OT_CLAIMS TABLE
**Purpose:** Tracks how staff members claim their overtime (replacement leave or payroll)

### Columns:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `claim_type` - Enum: 'replacement_leave' or 'payroll'
- `fulltime_hours` - Fulltime OT hours to claim
- `public_holiday_hours` - Public holiday OT hours to claim
- `replacement_days` - Calculated: (fulltime_hours + public_holiday_hours) / 8
- `status` - Enum: 'pending', 'approved', 'rejected'
- `remarks` - Admin notes
- `created_at`, `updated_at` - Timestamps

### Business Logic:
- **Conversion Rate:** 8 OT hours = 1 replacement leave day
- **Claim Options:**
  - **Manager/Supervisor:** Can ONLY claim for replacement leave (not payroll)
  - **Others:** Can choose replacement leave OR payroll

- **Payroll Calculation (when claim_type = 'payroll'):**
  - Fulltime OT: `fulltime_hours × RM12.26`
  - Public Holiday OT: `public_holiday_hours × RM21.68`
  - Total: Sum of both

---

## 3. LEAVES TABLE (UPDATED)
**Purpose:** Tracks all leave applications with automatic approval system

### New/Updated Columns:
- `department` - Denormalized from staff for constraint checking
- `auto_approved` - Boolean: true if approved by system, false if manual
- `approved_at` - Timestamp when approved

### Leave Types & Entitlements:
- Annual: 14 days max
- Hospitalization: 30 days max
- Medical: 14 days max
- Emergency: 7 days max
- Marriage: 3 days max
- Replacement: Calculated from OT hours (8 hours = 1 day)
- Unpaid: Unlimited

### Automatic Approval Logic:
**All leave types auto-approve EXCEPT replacement leave, which requires admin approval**

**Rejection Conditions:**
1. Insufficient leave balance
2. Department constraint violation

**Department Leave Constraints (max people per day):**
- Waiter: 4 people
- Kitchen: 3 people
- Cashier: 2 people
- Barista: 2 people
- Joki: 2 people

**Manager/Supervisor Constraint:**
- Maximum 2 days per week

**When Auto-Approved:**
- Status: 'approved'
- auto_approved: true
- approved_at: current timestamp
- Admin is notified for record-keeping and timetable updates

**When Rejected:**
- Status: 'rejected'
- auto_approved: false
- No admin notification (system auto-rejected)

**Replacement Leave:**
- Status: 'pending' (always requires admin review)
- requires_approval: true
- Auto-approval happens ONLY after admin approves OT claims
- Creates automatic leave entry when OT claim is approved

---

## 4. PAYROLLS TABLE
**Purpose:** Stores calculated payroll for each staff member per month

### Columns:
- `id` - Primary key
- `user_id` - Foreign key to users table
- `year` - Year of payroll (e.g., 2025)
- `month` - Month of payroll (1-12)
- `basic_salary` - From staff.salary
- `fixed_commission` - RM200 (only after 3 months of employment)
- `public_holiday_hours` - Hours worked on public holidays
- `public_holiday_pay` - public_holiday_hours × RM15.38
- `fulltime_ot_hours` - OT hours claimed for payroll
- `fulltime_ot_pay` - fulltime_ot_hours × RM12.26
- `public_holiday_ot_hours` - Public holiday OT hours claimed for payroll
- `public_holiday_ot_pay` - public_holiday_ot_hours × RM21.68
- `gross_salary` - Sum of all above components
- `total_deductions` - Deductions (if any)
- `net_salary` - gross_salary - total_deductions
- `status` - Enum: 'draft', 'approved', 'paid'
- `payment_date` - Date of payment
- `remarks` - Admin notes
- `created_at`, `updated_at` - Timestamps

### Payroll Calculation Formula:
```
Gross Salary = Basic Salary 
             + Fixed Commission (after 3 months)
             + Public Holiday Pay (RM15.38 × hours)
             + Fulltime OT Pay (RM12.26 × hours)
             + Public Holiday OT Pay (RM21.68 × hours)

Net Salary = Gross Salary - Total Deductions
```

### Example for Cashier:
```
Basic Salary:              RM1500.00
Fixed Commission:          RM200.00 (if eligible)
Public Holiday Hours:      7.5 hours
Public Holiday Pay:        7.5 × RM15.38 = RM115.35
Fulltime OT (claimed):     8 hours
Fulltime OT Pay:           8 × RM12.26 = RM98.08
Public Holiday OT:         4 hours
Public Holiday OT Pay:     4 × RM21.68 = RM86.72
─────────────────────────────────────
Gross Salary:              RM2000.15
Deductions:                RM0.00
Net Salary:                RM2000.15
```

---

## 5. RELATIONSHIPS

### User → Leaves (One to Many)
- One user has many leave applications

### User → Overtimes (One to Many)
- One user has many OT applications

### User → OTClaims (One to Many)
- One user has many OT claims (replacement or payroll)

### User → Payrolls (One to Many)
- One user has many payroll records (one per month)

### Staff → Leaves (Through User)
- Access staff member's leaves

### Staff → Overtimes (Through User)
- Access staff member's OT records

---

## 6. WORKFLOW FLOWS

### Leave Application Flow:
```
1. Staff submits leave application
2. System calculates days and validates:
   - Check leave balance
   - Check department constraints
   - Check if manager/supervisor (2 days/week limit)
3. If all checks pass:
   - Auto-approve
   - Set auto_approved = true
   - Notify admin (for record & timetable)
4. If checks fail:
   - Auto-reject
   - Set status = rejected
5. Replacement leave always goes to pending for admin review
```

### Overtime Application Flow:
```
1. Staff applies for OT on specific date
2. System checks:
   - Department OT limit for that day
   - Max hours per person (2 or 4)
3. If approved:
   - Auto-approved
   - Available for claiming
4. Staff can claim via OT_CLAIMS table
```

### OT Claim to Replacement Leave Flow:
```
1. Staff submits OT claim (claim_type = 'replacement_leave')
2. System calculates replacement_days (hours / 8)
3. Admin reviews and approves OT claim
4. System automatically creates Leave record:
   - leave_type = 'replacement'
   - total_days = calculated replacement_days
   - status = 'approved'
   - auto_approved = true
5. Staff can now use replacement leave
```

### OT Claim to Payroll Flow:
```
1. Staff submits OT claim (claim_type = 'payroll')
2. Admin approves OT claim
3. At end of month:
   - Admin triggers payroll generation
   - System sums all approved OT claims for payroll
   - Creates Payroll record with calculations
   - Admin reviews and approves
   - Marks as paid
```

---

## 7. KEY CONSTRAINTS & VALIDATIONS

### Overtime:
- Maximum OT applications per person per day: 1
- Hours must be within department/role limits
- OT date must be future or current date

### Leave:
- Start date ≤ End date
- Leave balance must not be exceeded
- Department capacity must not be exceeded

### OT Claims:
- Can only claim approved OT hours
- Cannot claim more than available
- Manager/Supervisor cannot claim for payroll

### Payroll:
- One payroll record per user per month
- Can only be generated after OT claims are finalized
- Payment date optional until marked as paid

---

## 8. ADMIN NOTIFICATIONS

Admin receives notifications for:
1. **Auto-approved leaves** - For record-keeping and timetable updates
2. **Replacement leave claims pending approval** - Admin must review
3. **Payroll ready for approval** - Review before finalizing
4. **Rejected leaves** - Optional, depending on policy

