# Staff Dashboard Design - Leave & Payroll Summary

## Overview
The staff dashboard has been enhanced with a comprehensive **Leave & Payroll Summary** section that provides at-a-glance insights into employee leave balances, payroll information, and overtime status.

---

## Dashboard Architecture

### 1. **Top Summary Section** (Always Visible)
Quick stats displayed as 4 cards showing:
- **Remaining Leave**: Shows days remaining out of total allocated leave
- **Used Leave**: Displays used days and percentage of total
- **Pending Requests**: Count of pending leave/OT applications
- **OT Claims**: Number of overtime claims filed this year

**Color Scheme:**
- Remaining Leave: Blue gradient
- Used Leave: Orange gradient  
- Pending Requests: Purple gradient
- OT Claims: Green gradient

---

### 2. **Notification Cards Grid** (4 Main Cards + 5th Hidden Card)

#### Card 1: User Information
- Username, Employee ID, Status
- Header: Purple gradient

#### Card 2: Overtime Approved
- Shows last 2 approved OT records
- Badge showing total approved count
- Header: Orange gradient

#### Card 3: Salary Claims Status
- Displays recent payroll claims
- Shows amount and status (Approved/Rejected/Pending)
- Header: Green gradient

#### Card 4: Replacement Leave Claims
- Recent replacement leave approvals
- Shows days and status
- Header: Purple gradient

#### Card 5: Leave & Payroll Summary (NEW)
- **Hidden by default** - Reveals comprehensive dashboard on click
- Badge: "Summary"
- Header: Pink gradient
- Button: "View Summary →"

---

## Hidden Leave & Payroll Summary Section

### Layout: 3 Main Widgets + Additional Sections

#### **Widget 1: Leave Balance Overview**
**Location:** Top-Left (50% width on desktop)
**Color Theme:** Blue accent (border-left)

**Contents:**
- **Progress Bar:** Visual representation of leave usage percentage
- **Three Stat Cards:**
  - Total Days (Blue)
  - Used Days (Orange)
  - Remaining Days (Green)
- **Breakdown by Type:** Table showing:
  - Leave type name
  - Days used vs. total
  - Remaining days for each type

**Key Metrics Displayed:**
- Actual numbers with 1 decimal place
- Percentage of usage
- Per-leave-type breakdown

---

#### **Widget 2: Payroll & Overtime Overview**
**Location:** Top-Right (50% width on desktop)
**Color Theme:** Green accent (border-left)

**Contents:**
- **Three Stat Cards:**
  - OT Hours (Green)
  - Total Claims Amount (Purple)
  - Approved OT Count (Yellow)
- **Overtime Status Section:**
  - Approved OT count (green indicator)
  - Pending OT count (yellow indicator)
  - Salary Claims count (blue indicator)

**Key Metrics:**
- Total OT hours worked
- Total OT pay claimed (RM)
- Breakdown of statuses

---

#### **Widget 3: Recent Leave Applications**
**Location:** Full Width (Below Widgets 1 & 2)
**Color Theme:** Indigo accent (border-left)

**Contents:**
Individual leave application cards showing:
- Leave type name
- Status badge (✓ Approved, ✗ Rejected, ⏳ Pending)
- Date range (start - end)
- Number of days
- Application date (relative time: "2 days ago")

**Display:** Up to 5 most recent applications
**States:**
- Approved: Green styling
- Rejected: Red styling
- Pending: Yellow styling

---

#### **Widget 4: Recent Salary Claims**
**Location:** Full Width
**Color Theme:** Green accent (border-left)

**Contents:**
Data table with columns:
| Period | Hours | Amount (RM) | Status |
|--------|-------|------------|--------|
| January 2026 | 24.5 | 445.67 | Approved |
| December 2025 | 18.0 | 320.50 | Pending |

**Details per row:**
- Month-Year of claim
- Total overtime hours claimed
- Total payroll amount in RM
- Current status with color-coded badge

---

## Data Aggregation Logic

### Leave Balance Calculation
```php
Total Leave = Sum of all LeaveBalance.total_days for staff
Used Leave = Sum of all LeaveBalance.used_days for staff
Remaining = Total - Used
Usage % = (Used / Total) × 100
```

### Payroll Calculation
```php
For each OTClaim (claim_type = 'payroll'):
  - Retrieve fulltime_hours and public_holiday_hours
  - Calculate amounts:
    - Fulltime Pay = fulltime_hours × RM 12.26
    - Public Holiday Pay = public_holiday_hours × RM 21.68
    - Total Pay = Fulltime Pay + Public Holiday Pay
  
Total OT Pay = Sum of all approved claims
```

### Recent Leave Applications
```php
Query: Leave records for staff
Filter: Ordered by created_at DESC
Limit: Last 5 records
Status: approved / rejected / pending
```

---

## Visual Design System

### Color Palette
- **Primary Blues:** #3B82F6 (Leave)
- **Primary Greens:** #10B981 (Payroll/OT)
- **Primary Purples:** #8B5CF6 (Status/Actions)
- **Accent Orange:** #F59E0B (Used/Alerts)
- **Accent Pink:** #EC4899 (Summary Card)
- **Backgrounds:** Gray-50, Gray-100 (light)

### Typography
- **Headings:** Font-bold (text-lg, text-xl, text-2xl)
- **Labels:** Font-semibold (text-sm)
- **Values:** Font-bold (text-2xl for stats)
- **Body:** Regular (text-sm for descriptions)

### Spacing & Sizing
- **Card Padding:** 6 units (1.5rem) on desktop, 4 units on tablet
- **Gap Between Cards:** 4-6 units
- **Border Radius:** lg (0.5rem) for cards, full for badges
- **Borders:** 1-4px left border for emphasis

### Interactive Elements
- **Buttons:** Hover state with opacity/color change
- **Progress Bar:** Smooth width animation (duration-500)
- **Toggle:** Smooth show/hide with smooth scroll
- **Table Rows:** Hover background effect
- **Icons:** 5-6 unit sizing with consistent strokes

---

## Responsive Design

### Breakpoints
- **Mobile:** Single column for all widgets
- **Tablet (md):** 2-column layout for summary stats
- **Desktop (lg):** 4-column summary grid, 2-column widget layout
- **Large Screen (xl):** Full optimization

### Mobile Considerations
- Cards stack vertically
- Full-width sections
- Reduced padding
- Simplified tables (scroll horizontally if needed)
- Text size adjustments

---

## Toggle Functionality

### Show Summary
```javascript
showLeavePayrollSummarySection()
```
- Removes 'hidden' class from section
- Scrolls to section smoothly (with 100px offset)

### Hide Summary
```javascript
hideLeavePayrollSummarySection()
```
- Adds 'hidden' class to section
- No scroll action

---

## Database Models Used

1. **LeaveBalance** - Staff leave allocations and usage
2. **Leave** - Individual leave applications
3. **Overtime** - Approved/pending overtime records
4. **OTClaim** - Salary claims and replacement leave claims
5. **LeaveType** - Leave type classifications
6. **Staff** - Staff information (linked to User)

---

## Key Features

✅ **Quick Summary Stats** - 4-card overview at top
✅ **Comprehensive Dashboard** - Expandable full summary
✅ **Progress Visualization** - Leave usage progress bar
✅ **Breakdown by Type** - Leave details per type
✅ **Recent Applications** - Last 5 leave requests with status
✅ **Payroll Table** - Historical salary claims view
✅ **Status Indicators** - Color-coded badges (Approved/Rejected/Pending)
✅ **Responsive Design** - Mobile, tablet, desktop optimized
✅ **Smooth Transitions** - Animated section reveal and scroll
✅ **Accessibility** - Clear labels, icons, and semantic HTML

---

## Future Enhancement Opportunities

1. **Charts & Graphs**
   - Leave usage pie chart
   - OT trends line chart
   - Monthly payroll bar chart

2. **Filters & Sorting**
   - Filter by leave type
   - Date range selector
   - Sort by amount/date

3. **Export Functionality**
   - Download leave summary as PDF
   - Export payroll records to CSV
   - Print-friendly view

4. **Notifications**
   - Leave balance alerts
   - Pending request reminders
   - Payroll processing status

5. **Analytics**
   - Leave pattern analysis
   - OT frequency tracking
   - Department comparisons

---

## Implementation Notes

- All calculations done server-side with Blade templating
- Database queries optimized with eager loading
- Responsive grid system using Tailwind CSS
- Smooth animations using CSS transitions
- JavaScript for show/hide toggle functionality
- No external charting libraries required (can be added)

