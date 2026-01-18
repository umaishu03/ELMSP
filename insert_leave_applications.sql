-- =========================================================
-- LEAVE APPLICATIONS & PAYROLL SEED DATA
-- October 2025 - January 2026
-- =========================================================
-- PURPOSE:
-- This seed file creates realistic leave applications across
-- 4 months to test the leave approval process, payroll
-- deduction, and shift updates.
--
-- FEATURES:
-- 1. Approved leave records for randomly selected staff
-- 2. Multiple leave types including unpaid leave
-- 3. Realistic distribution across Oct, Nov, Dec, Jan
-- 4. Corresponding shift records updated to reflect approved leaves
-- 5. Leave balance records updated
-- 6. Payroll records with deductions for unpaid leaves
-- 7. Manager/Supervisor constraints respected (2 days/week max)
-- 8. Department constraints respected
--
-- NOTES:
-- - Not all staff have leave records (realistic scenario)
-- - Unpaid leave is included for payroll deduction testing
-- - Shift records marked as rest_day for approved leave dates
-- - Leave balance records automatically calculated based on used days
-- =========================================================

-- =========================================================
-- LEAVE TYPES (already exists from LeaveTypesSeeder, just reference)
-- =========================================================
-- Leave types are:
-- 1: annual (max 14 days, auto-approve)
-- 2: hospitalization (max 30 days, auto-approve)
-- 3: medical (max 14 days, auto-approve)
-- 4: emergency (max 7 days, auto-approve)
-- 5: replacement (no max, requires_approval=1)
-- 6: marriage (max 3 days, auto-approve)
-- 7: unpaid (max 10 days, auto-approve, no deduction)


-- =========================================================
-- INSERT INTO LEAVES TABLE
-- Approved leaves for Oct-Jan 2025-2026
-- =========================================================

-- =========================================================
-- OCTOBER 2025 APPROVED LEAVES
-- =========================================================

-- YS402 (Supervisor) - 2 days annual leave (Wed-Thu, within 2-day/week limit)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS402'), 3, '2025-10-08', '2025-10-09', 2, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS403 (Cashier) - 1 day medical leave (Thu)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS403'), 3, '2025-10-09', '2025-10-09', 1, 'Medical leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS406 (Barista) - 1 day unpaid leave (Mon) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 7, '2025-10-06', '2025-10-06', 1, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS410 (Waiter) - 1 day emergency leave (Fri)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS410'), 4, '2025-10-10', '2025-10-10', 1, 'Emergency family matter', 'approved', 1, NOW(), NOW(), NOW());

-- YS413 (Waiter) - 2 days annual leave (Mon-Tue)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS413'), 1, '2025-10-13', '2025-10-14', 2, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS419 (Kitchen) - 3 days medical leave (Wed-Fri)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS419'), 3, '2025-10-15', '2025-10-17', 3, 'Medical leave - doctor prescribed rest', 'approved', 1, NOW(), NOW(), NOW());

-- YS404 (Cashier) - 1 day unpaid leave (Tue) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS404'), 7, '2025-10-28', '2025-10-28', 1, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- =========================================================
-- NOVEMBER 2025 APPROVED LEAVES
-- =========================================================

-- YS401 (Manager) - 1 day annual leave (Mon) - within 2-day/week limit
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS401'), 1, '2025-11-03', '2025-11-03', 1, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS405 (Cashier) - 2 days marriage leave (Thu-Fri)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS405'), 6, '2025-11-06', '2025-11-07', 2, 'Marriage leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS407 (Barista) - 1 day medical leave (Tue)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS407'), 3, '2025-11-11', '2025-11-11', 1, 'Dental appointment', 'approved', 1, NOW(), NOW(), NOW());

-- YS411 (Waiter) - 1 day unpaid leave (Wed) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS411'), 7, '2025-11-12', '2025-11-12', 1, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS414 (Waiter) - 2 days annual leave (Mon-Tue)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS414'), 1, '2025-11-17', '2025-11-18', 2, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS420 (Kitchen) - 3 days hospitalization leave (Thu-Fri-Mon)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS420'), 2, '2025-11-20', '2025-11-24', 3, 'Hospitalization and recovery', 'approved', 1, NOW(), NOW(), NOW());

-- YS408 (Joki) - 1 day emergency leave (Fri)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS408'), 4, '2025-11-28', '2025-11-28', 1, 'Family emergency', 'approved', 1, NOW(), NOW(), NOW());

-- =========================================================
-- DECEMBER 2025 APPROVED LEAVES
-- =========================================================

-- YS402 (Supervisor) - 2 days annual leave (Mon-Tue) - within 2-day/week limit
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS402'), 1, '2025-12-01', '2025-12-02', 2, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS409 (Joki) - 1 day medical leave (Wed)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS409'), 3, '2025-12-03', '2025-12-03', 1, 'Medical check-up', 'approved', 1, NOW(), NOW(), NOW());

-- YS415 (Waiter) - 2 days unpaid leave (Thu-Fri) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS415'), 7, '2025-12-04', '2025-12-05', 2, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS421 (Kitchen) - 2 days annual leave (Mon-Tue)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS421'), 1, '2025-12-08', '2025-12-09', 2, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS416 (Waiter) - 1 day emergency leave (Wed)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS416'), 4, '2025-12-10', '2025-12-10', 1, 'Emergency - child illness', 'approved', 1, NOW(), NOW(), NOW());

-- YS423 (Kitchen) - 1 day unpaid leave (Fri) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS423'), 7, '2025-12-12', '2025-12-12', 1, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS412 (Waiter) - 3 days annual leave (Mon-Tue-Wed)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS412'), 1, '2025-12-15', '2025-12-17', 3, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- =========================================================
-- JANUARY 2026 APPROVED LEAVES
-- =========================================================

-- YS401 (Manager) - 1 day annual leave (Tue) - within 2-day/week limit
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS401'), 1, '2026-01-06', '2026-01-06', 1, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS406 (Barista) - 2 days unpaid leave (Thu-Fri) - for payroll deduction test
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 7, '2026-01-08', '2026-01-09', 2, 'Unpaid leave', 'approved', 1, NOW(), NOW(), NOW());

-- YS417 (Waiter) - 1 day medical leave (Wed)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS417'), 3, '2026-01-14', '2026-01-14', 1, 'Medical consultation', 'approved', 1, NOW(), NOW(), NOW());

-- YS422 (Kitchen) - 1 day annual leave (Fri)
INSERT INTO `leaves` (`staff_id`, `leave_type_id`, `start_date`, `end_date`, `total_days`, `reason`, `status`, `auto_approved`, `approved_at`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS422'), 1, '2026-01-16', '2026-01-16', 1, 'Annual leave', 'approved', 1, NOW(), NOW(), NOW());



-- =========================================================
-- UPDATE SHIFT RECORDS FOR APPROVED LEAVES
-- Set leave_id and rest_day=1 to link shifts to leave applications
-- This establishes the relationship between shift and leave records
-- =========================================================

-- October 2025 leaves - update shifts with leave_id
UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS402') AND start_date='2025-10-08' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS402') AND `date`='2025-10-08';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS402') AND start_date='2025-10-08' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS402') AND `date`='2025-10-09';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS403') AND start_date='2025-10-09' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS403') AND `date`='2025-10-09';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS406') AND start_date='2025-10-06' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS406') AND `date`='2025-10-06';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS410') AND start_date='2025-10-10' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS410') AND `date`='2025-10-10';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS413') AND start_date='2025-10-13' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS413') AND `date` IN ('2025-10-13', '2025-10-14');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS419') AND start_date='2025-10-15' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS419') AND `date` IN ('2025-10-15', '2025-10-16', '2025-10-17');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS404') AND start_date='2025-10-28' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS404') AND `date`='2025-10-28';

-- November 2025 leaves - update shifts with leave_id
UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS401') AND start_date='2025-11-03' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS401') AND `date`='2025-11-03';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS405') AND start_date='2025-11-06' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS405') AND `date` IN ('2025-11-06', '2025-11-07');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS407') AND start_date='2025-11-11' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS407') AND `date`='2025-11-11';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS411') AND start_date='2025-11-12' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS411') AND `date`='2025-11-12';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS414') AND start_date='2025-11-17' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS414') AND `date` IN ('2025-11-17', '2025-11-18');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS420') AND start_date='2025-11-20' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS420') AND `date` IN ('2025-11-20', '2025-11-21', '2025-11-24');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS408') AND start_date='2025-11-28' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS408') AND `date`='2025-11-28';

-- December 2025 leaves - update shifts with leave_id
UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS402') AND start_date='2025-12-01' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS402') AND `date` IN ('2025-12-01', '2025-12-02');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS409') AND start_date='2025-12-03' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS409') AND `date`='2025-12-03';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS415') AND start_date='2025-12-04' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS415') AND `date` IN ('2025-12-04', '2025-12-05');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS421') AND start_date='2025-12-08' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS421') AND `date` IN ('2025-12-08', '2025-12-09');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS416') AND start_date='2025-12-10' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS416') AND `date`='2025-12-10';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS423') AND start_date='2025-12-12' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS423') AND `date`='2025-12-12';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS412') AND start_date='2025-12-15' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS412') AND `date` IN ('2025-12-15', '2025-12-16', '2025-12-17');

-- January 2026 leaves - update shifts with leave_id
UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS401') AND start_date='2026-01-06' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS401') AND `date`='2026-01-06';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS406') AND start_date='2026-01-08' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS406') AND `date` IN ('2026-01-08', '2026-01-09');

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS417') AND start_date='2026-01-14' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS417') AND `date`='2026-01-14';

UPDATE `shifts` SET `rest_day`=1, `start_time`='', `end_time`='', `break_minutes`=0, `leave_id`=(SELECT id FROM leaves WHERE staff_id=(SELECT id FROM staff WHERE employee_id='YS422') AND start_date='2026-01-16' LIMIT 1)
WHERE `staff_id`=(SELECT id FROM staff WHERE employee_id='YS422') AND `date`='2026-01-16';



-- =========================================================
-- END OF LEAVE APPLICATIONS SEED DATA
-- =========================================================
