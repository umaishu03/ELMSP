-- =========================================================
-- OVERTIME APPLICATIONS SEED DATA
-- October, November, December 2025
-- =========================================================
-- PURPOSE:
-- This seed file creates realistic overtime records across
-- 3 months to test the overtime approval process.
--
-- FEATURES:
-- 1. Approved overtime records for randomly selected staff
-- 2. Realistic OT duration (up to 4 hours)
-- 3. OT must end no later than 11:00 PM (23:00)
-- 4. Only staff whose shifts end before 23:00 can have OT
-- 5. OT types: fulltime (RM12.26/hr), public_holiday (RM21.68/hr)
-- 6. Realistic distribution across Oct, Nov, Dec
--
-- SYSTEM RULES:
-- - Overtime duration: up to 4 hours maximum
-- - Overtime end time: must be <= 23:00 (11:00 PM)
-- - If a staff's normal shift already ends at 23:00, they CANNOT have OT
-- - All OT records in this seed are pre-approved
--
-- STAFF EXCLUDED FROM OVERTIME (work until 23:00):
-- - YS401 (Manager): 10:00-23:00
-- - YS402 (Supervisor): 07:00-23:00
-- - YS405, YS407, YS409, YS412, YS415, YS418, YS421, YS424
--
-- STAFF ELIGIBLE FOR OVERTIME (shifts end before 23:00):
-- - Cashiers: YS403 (14:30), YS404 (17:30)
-- - Barista: YS406 (14:30)
-- - Joki: YS408 (17:30)
-- - Waiters: YS410 (14:30), YS411 (17:30), YS413 (14:30), YS414 (17:30), YS416 (14:30), YS417 (17:30)
-- - Kitchen: YS419 (14:30), YS420 (17:30), YS422 (14:30), YS423 (17:30)
-- =========================================================

-- =========================================================
-- OCTOBER 2025 APPROVED OVERTIMES
-- =========================================================

-- YS403 (Cashier) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 3
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS403'), 'fulltime', '2025-10-03', 4.00, 'approved', 'Approved overtime - inventory count', NOW(), NOW());

-- YS404 (Cashier) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 6
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS404'), 'fulltime', '2025-10-06', 4.00, 'approved', 'Approved overtime - year-end stock take', NOW(), NOW());

-- YS406 (Barista) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 18:30-22:30 (4 hours) on Oct 8
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 'fulltime', '2025-10-08', 4.00, 'approved', 'Approved overtime - event preparation', NOW(), NOW());

-- YS408 (Joki) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 10
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS408'), 'fulltime', '2025-10-10', 4.00, 'approved', 'Approved overtime - delivery assistance', NOW(), NOW());

-- YS410 (Waiter) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 12
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS410'), 'fulltime', '2025-10-12', 4.00, 'approved', 'Approved overtime - private function', NOW(), NOW());

-- YS411 (Waiter) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 15
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS411'), 'fulltime', '2025-10-15', 4.00, 'approved', 'Approved overtime - kitchen support', NOW(), NOW());

-- YS413 (Waiter) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 18:30-22:30 (4 hours) on Oct 18
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS413'), 'fulltime', '2025-10-18', 4.00, 'approved', 'Approved overtime - server coverage', NOW(), NOW());

-- YS414 (Waiter) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 20 (Deepavali/Diwali - Public Holiday)
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS414'), 'public_holiday', '2025-10-20', 4.00, 'approved', 'Approved overtime - deepavali celebration', NOW(), NOW());

-- YS416 (Waiter) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 22
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS416'), 'fulltime', '2025-10-22', 4.00, 'approved', 'Approved overtime - event setup', NOW(), NOW());

-- YS417 (Waiter) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 25
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS417'), 'fulltime', '2025-10-25', 4.00, 'approved', 'Approved overtime - wedding reception', NOW(), NOW());

-- YS419 (Kitchen) - shift ends 14:30, can OT until 23:00 (max 8.5 hrs, limit to 4 hrs)
-- OT: 18:00-22:00 (4 hours) on Oct 28
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS419'), 'fulltime', '2025-10-28', 4.00, 'approved', 'Approved overtime - catering preparation', NOW(), NOW());

-- YS420 (Kitchen) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Oct 30
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS420'), 'fulltime', '2025-10-30', 4.00, 'approved', 'Approved overtime - special orders', NOW(), NOW());

-- =========================================================
-- NOVEMBER 2025 APPROVED OVERTIMES
-- =========================================================

-- YS403 (Cashier) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Nov 5
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS403'), 'fulltime', '2025-11-05', 4.00, 'approved', 'Approved overtime - cash handling', NOW(), NOW());

-- YS404 (Cashier) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 8
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS404'), 'fulltime', '2025-11-08', 4.00, 'approved', 'Approved overtime - sales reporting', NOW(), NOW());

-- YS406 (Barista) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Nov 12
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 'fulltime', '2025-11-12', 4.00, 'approved', 'Approved overtime - cafe preparation', NOW(), NOW());

-- YS408 (Joki) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 14
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS408'), 'fulltime', '2025-11-14', 4.00, 'approved', 'Approved overtime - delivery coordination', NOW(), NOW());

-- YS410 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Nov 16
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS410'), 'fulltime', '2025-11-16', 4.00, 'approved', 'Approved overtime - dinner service', NOW(), NOW());

-- YS411 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 19
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS411'), 'fulltime', '2025-11-19', 4.00, 'approved', 'Approved overtime - group booking', NOW(), NOW());

-- YS413 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Nov 22
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS413'), 'fulltime', '2025-11-22', 4.00, 'approved', 'Approved overtime - banquet service', NOW(), NOW());

-- YS414 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 26
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS414'), 'fulltime', '2025-11-26', 4.00, 'approved', 'Approved overtime - closing procedures', NOW(), NOW());

-- YS416 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Nov 28
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS416'), 'fulltime', '2025-11-28', 4.00, 'approved', 'Approved overtime - conference attendance', NOW(), NOW());

-- YS417 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 30
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS417'), 'fulltime', '2025-11-30', 4.00, 'approved', 'Approved overtime - month-end closing', NOW(), NOW());

-- YS419 (Kitchen) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Dec 2
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS419'), 'fulltime', '2025-12-02', 4.00, 'approved', 'Approved overtime - menu testing', NOW(), NOW());

-- YS420 (Kitchen) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Nov 4
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS420'), 'fulltime', '2025-11-04', 4.00, 'approved', 'Approved overtime - stock preparation', NOW(), NOW());

-- =========================================================
-- DECEMBER 2025 APPROVED OVERTIMES
-- =========================================================

-- YS403 (Cashier) - shift ends 14:30
-- OT: 18:00-22:00 (4 hours) on Dec 5
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS403'), 'fulltime', '2025-12-05', 4.00, 'approved', 'Approved overtime - year-end closing', NOW(), NOW());

-- YS404 (Cashier) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 8
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS404'), 'fulltime', '2025-12-08', 4.00, 'approved', 'Approved overtime - auditing', NOW(), NOW());

-- YS406 (Barista) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Dec 10
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 'fulltime', '2025-12-10', 4.00, 'approved', 'Approved overtime - holiday celebration setup', NOW(), NOW());

-- YS408 (Joki) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 12
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS408'), 'fulltime', '2025-12-12', 4.00, 'approved', 'Approved overtime - year-end delivery', NOW(), NOW());

-- YS410 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Dec 15
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS410'), 'fulltime', '2025-12-15', 4.00, 'approved', 'Approved overtime - christmas party service', NOW(), NOW());

-- YS411 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 18
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS411'), 'fulltime', '2025-12-18', 4.00, 'approved', 'Approved overtime - festive menu service', NOW(), NOW());

-- YS413 (Waiter) - shift ends 14:30
-- OT: 18:30-22:30 (4 hours) on Dec 20
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS413'), 'fulltime', '2025-12-20', 4.00, 'approved', 'Approved overtime - year-end event', NOW(), NOW());

-- YS414 (Waiter) - shift ends 17:30, can OT until 23:00 (max 5.5 hrs, use 4 hrs)
-- OT: 19:00-23:00 (4 hours) on Dec 25 (Christmas Day - Public Holiday)
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS414'), 'public_holiday', '2025-12-25', 4.00, 'approved', 'Approved overtime - christmas day celebration', NOW(), NOW());

-- YS416 (Waiter) - shift ends 14:30
-- OT: 18:00-22:00 (4 hours) on Dec 26
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS416'), 'fulltime', '2025-12-26', 4.00, 'approved', 'Approved overtime - boxing day service', NOW(), NOW());

-- YS417 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 29
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS417'), 'fulltime', '2025-12-29', 4.00, 'approved', 'Approved overtime - new year preparation', NOW(), NOW());

-- YS419 (Kitchen) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Dec 3
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS419'), 'fulltime', '2025-12-03', 4.00, 'approved', 'Approved overtime - menu development', NOW(), NOW());

-- YS420 (Kitchen) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 7
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS420'), 'fulltime', '2025-12-07', 4.00, 'approved', 'Approved overtime - special ingredients prep', NOW(), NOW());

-- YS422 (Kitchen) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Dec 11
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS422'), 'fulltime', '2025-12-11', 4.00, 'approved', 'Approved overtime - festival meal prep', NOW(), NOW());

-- YS423 (Kitchen) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Dec 16
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS423'), 'fulltime', '2025-12-16', 4.00, 'approved', 'Approved overtime - catering service', NOW(), NOW());

-- =========================================================
-- JANUARY 2026 APPROVED OVERTIMES
-- =========================================================

-- YS403 (Cashier) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Jan 2
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS403'), 'fulltime', '2026-01-02', 4.00, 'approved', 'Approved overtime - new year setup', NOW(), NOW());

-- YS404 (Cashier) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Jan 5
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS404'), 'fulltime', '2026-01-05', 4.00, 'approved', 'Approved overtime - stock management', NOW(), NOW());

-- YS406 (Barista) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Jan 8
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS406'), 'fulltime', '2026-01-08', 4.00, 'approved', 'Approved overtime - cafe inventory', NOW(), NOW());

-- YS408 (Joki) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Jan 10
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS408'), 'fulltime', '2026-01-10', 4.00, 'approved', 'Approved overtime - delivery support', NOW(), NOW());

-- YS410 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Jan 12
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS410'), 'fulltime', '2026-01-12', 4.00, 'approved', 'Approved overtime - team training', NOW(), NOW());

-- YS411 (Waiter) - shift ends 17:30
-- OT: 19:00-23:00 (4 hours) on Jan 15
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS411'), 'fulltime', '2026-01-15', 4.00, 'approved', 'Approved overtime - special event', NOW(), NOW());

-- YS413 (Waiter) - shift ends 14:30
-- OT: 19:00-23:00 (4 hours) on Jan 15
INSERT INTO `overtimes` (`staff_id`, `ot_type`, `ot_date`, `hours`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
((SELECT id FROM staff WHERE employee_id='YS413'), 'fulltime', '2026-01-15', 4.00, 'approved', 'Approved overtime - special event', NOW(), NOW());

-- =========================================================
-- UPDATE SHIFTS TABLE FOR OVERTIME DATES
-- 1. Link shift records to overtime applications via overtime_id
-- 2. Update shift end_time to reflect overtime hours
-- =========================================================

-- October 2025 overtime shifts
UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `ot_date` = '2025-10-03' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `date` = '2025-10-03';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `ot_date` = '2025-10-06' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `date` = '2025-10-06';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `ot_date` = '2025-10-08' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `date` = '2025-10-08';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `ot_date` = '2025-10-10' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `date` = '2025-10-10';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `ot_date` = '2025-10-12' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `date` = '2025-10-12';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `ot_date` = '2025-10-15' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `date` = '2025-10-15';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `ot_date` = '2025-10-18' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `date` = '2025-10-18';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `ot_date` = '2025-10-20' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `date` = '2025-10-20';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `ot_date` = '2025-10-22' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `date` = '2025-10-22';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `ot_date` = '2025-10-25' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `date` = '2025-10-25';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `ot_date` = '2025-10-28' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `date` = '2025-10-28';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `ot_date` = '2025-10-30' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `date` = '2025-10-30';

-- November 2025 overtime shifts
UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `ot_date` = '2025-11-05' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `date` = '2025-11-05';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `ot_date` = '2025-11-08' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `date` = '2025-11-08';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `ot_date` = '2025-11-12' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `date` = '2025-11-12';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `ot_date` = '2025-11-14' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `date` = '2025-11-14';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `ot_date` = '2025-11-16' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `date` = '2025-11-16';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `ot_date` = '2025-11-19' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `date` = '2025-11-19';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `ot_date` = '2025-11-22' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `date` = '2025-11-22';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `ot_date` = '2025-11-26' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `date` = '2025-11-26';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `ot_date` = '2025-11-28' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `date` = '2025-11-28';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `ot_date` = '2025-11-30' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `date` = '2025-11-30';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `ot_date` = '2025-11-04' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `date` = '2025-11-04';

-- December 2025 overtime shifts
UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `ot_date` = '2025-12-05' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `date` = '2025-12-05';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `ot_date` = '2025-12-08' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `date` = '2025-12-08';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `ot_date` = '2025-12-10' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `date` = '2025-12-10';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `ot_date` = '2025-12-12' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `date` = '2025-12-12';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `ot_date` = '2025-12-15' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `date` = '2025-12-15';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `ot_date` = '2025-12-18' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `date` = '2025-12-18';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `ot_date` = '2025-12-20' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `date` = '2025-12-20';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `ot_date` = '2025-12-25' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS414') AND `date` = '2025-12-25';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `ot_date` = '2025-12-26' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS416') AND `date` = '2025-12-26';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `ot_date` = '2025-12-29' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS417') AND `date` = '2025-12-29';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `ot_date` = '2025-12-03' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `date` = '2025-12-03';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `ot_date` = '2025-12-07' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS420') AND `date` = '2025-12-07';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS422') AND `ot_date` = '2025-12-11' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS422') AND `date` = '2025-12-11';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS423') AND `ot_date` = '2025-12-16' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS423') AND `date` = '2025-12-16';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `ot_date` = '2025-12-02' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS419') AND `date` = '2025-12-02';

-- January 2026 overtime shifts
UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `ot_date` = '2026-01-02' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS403') AND `date` = '2026-01-02';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `ot_date` = '2026-01-05' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS404') AND `date` = '2026-01-05';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `ot_date` = '2026-01-08' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS406') AND `date` = '2026-01-08';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `ot_date` = '2026-01-10' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS408') AND `date` = '2026-01-10';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `ot_date` = '2026-01-12' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS410') AND `date` = '2026-01-12';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `ot_date` = '2026-01-15' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS411') AND `date` = '2026-01-15';

UPDATE `shifts` SET `overtime_id` = (SELECT id FROM `overtimes` WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `ot_date` = '2026-01-15' LIMIT 1)
WHERE `staff_id` = (SELECT id FROM staff WHERE employee_id='YS413') AND `date` = '2026-01-15';

-- =========================================================
-- UPDATE SHIFT END_TIME TO REFLECT OVERTIME HOURS
-- =========================================================
-- Generic UPDATE: Add overtime hours to shift end_time for all shifts with overtime
UPDATE `shifts` s
INNER JOIN `overtimes` o ON s.overtime_id = o.id
SET s.end_time = ADDTIME(s.end_time, SEC_TO_TIME(o.hours * 3600))
WHERE s.overtime_id IS NOT NULL;

-- =========================================================
-- END OF OVERTIME SEED DATA
-- =========================================================
