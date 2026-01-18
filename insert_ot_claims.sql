-- =========================================================
-- OVERTIME CLAIMS SEED DATA
-- October 2025 - December 2025
-- =========================================================
-- PURPOSE:
-- This seed file creates realistic overtime claims to test the
-- overtime claim approval and replacement leave processes.
--
-- TABLE STRUCTURE:
-- ot_claims has TWO claim_type options:
--   1. 'replacement_leave': Staff claims OT hours as replacement days off
--   2. 'payroll': Used for salary/payroll processing
--
-- SCHEMA NOTES:
-- - user_id was REMOVED in migration 2025_11_27_000012
-- - User is identified through ot_ids → overtime records → staff → user
-- - Each overtime record can only be claimed ONCE (no overlaps allowed)
-- - Only overtimes with claimed=0 can be included in a new claim
-- - Once an overtime is used in ot_claims.ot_ids, claimed MUST be set to 1
-- - No overtime ID can appear in multiple claims
--
-- FIELDS:
-- - ot_ids: JSON array of overtime IDs included in this claim (REQUIRED, must exist)
-- - claim_type: 'replacement_leave' or 'payroll' (REQUIRED)
-- - fulltime_hours: Total fulltime OT hours
-- - public_holiday_hours: Total public holiday OT hours
-- - replacement_days: Calculated days (usually fulltime_hours / 8)
-- - status: 'pending', 'approved', 'rejected'
-- - remarks: Optional admin notes
--
-- BUSINESS LOGIC:
-- - When staff claim OT for replacement leave, combine fulltime + public_holiday OT
-- - 8 hours OT = 1 day replacement leave
-- - Public holiday OT can be claimed as cash via payroll claim (separate)
-- - All claimed overtimes get marked with claimed = 1 in overtimes table
-- - No overtime ID can appear in multiple claims
-- =========================================================

-- =========================================================
-- REPLACEMENT LEAVE CLAIMS
-- Staff claiming OT hours as days off (fulltime + public holiday hours)
-- OT IDs used: 2, 4, 5, 7, 11, 12, 14, 16, 17, 19, 23, 24
-- All claims match same staff_id: OT IDs must belong to same staff
-- =========================================================

-- YS404 (staff_id 4) - Replacement leave claim: OT IDs 2, 14 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(2, 14),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- YS408 (staff_id 8) - Replacement leave claim: OT IDs 4, 16 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(4, 16),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- YS410 (staff_id 10) - Replacement leave claim: OT IDs 5, 17 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(5, 17),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- YS413 (staff_id 13) - Replacement leave claim: OT IDs 7, 19 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(7, 19),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- YS419 (staff_id 19) - Replacement leave claim: OT IDs 11, 23 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(11, 23),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- YS420 (staff_id 20) - Replacement leave claim: OT IDs 12, 24 = 8 hours fulltime = 1 day
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(12, 24),
 'replacement_leave', 8.00, 0.00, 1.00, 'approved', 'October-November fulltime OT claimed as 1 replacement day', '2025-11-30', '2025-11-30');

-- =========================================================
-- PAYROLL CLAIMS
-- Staff claiming OT hours for salary processing (October-December)
-- OT IDs used: 1, 3, 6, 8, 9, 10, 13, 15, 18, 20, 21, 22 (Oct-Nov converted + staff 14), 25-38 (Dec)
-- =========================================================

-- Converted payroll claims (Oct-Nov)
-- YS403 (staff_id 3) - Payroll claim: OT ID 1 (Oct, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(1),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'October fulltime OT for payroll processing', '2025-10-31', '2025-10-31');

-- YS406 (staff_id 6) - Payroll claim: OT ID 3 (Oct, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(3),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'October fulltime OT for payroll processing', '2025-10-31', '2025-10-31');

-- YS411 (staff_id 11) - Payroll claim: OT ID 6 (Oct, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(6),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'October fulltime OT for payroll processing', '2025-10-31', '2025-10-31');

-- YS414 (staff_id 14) - Payroll claim: OT ID 8 (Oct, 4 PH )
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(8),
 'payroll', 0.00, 4.00, 0.00, 'approved', 'October mixed OT (Deepavali public holiday + fulltime) for payroll processing', '2025-10-31', '2025-10-31');

-- YS416 (staff_id 16) - Payroll claim: OT ID 9 (Oct, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(9),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'October fulltime OT for payroll processing', '2025-10-31', '2025-10-31');

-- YS417 (staff_id 17) - Payroll claim: OT ID 10 (Oct, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(10),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'October fulltime OT for payroll processing', '2025-10-31', '2025-10-31');

-- YS403 (staff_id 3) - Payroll claim: OT ID 13 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(13),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November fulltime OT for payroll processing', '2025-11-30', '2025-11-30');

-- YS406 (staff_id 6) - Payroll claim: OT ID 15 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(15),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November fulltime OT for payroll processing', '2025-11-30', '2025-11-30');

-- YS411 (staff_id 11) - Payroll claim: OT ID 18 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(18),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November fulltime OT for payroll processing', '2025-11-30', '2025-11-30');

-- YS414 (staff_id 14) - Payroll claim: OT ID 20 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(20),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November mixed OT (Deepavali public holiday + fulltime) for payroll processing', '2025-11-30', '2025-11-30');

-- YS416 (staff_id 16) - Payroll claim: OT ID 21 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(21),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November fulltime OT for payroll processing', '2025-11-30', '2025-11-30');

-- YS417 (staff_id 17) - Payroll claim: OT ID 22 (Nov, 4 FT)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(22),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'November fulltime OT for payroll processing', '2025-11-30', '2025-11-30');

-- YS403 (staff_id 3) - Payroll claim: OT ID 25 (Dec 5)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(25),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS404 (staff_id 4) - Payroll claim: OT ID 26 (Dec 8)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(26),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS406 (staff_id 6) - Payroll claim: OT ID 27 (Dec 10)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(27),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS408 (staff_id 8) - Payroll claim: OT ID 28 (Dec 12)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(28),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS410 (staff_id 10) - Payroll claim: OT ID 29 (Dec 15)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(29),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS411 (staff_id 11) - Payroll claim: OT ID 30 (Dec 18)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(30),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS413 (staff_id 13) - Payroll claim: OT ID 31 (Dec 20)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(31),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS414 (staff_id 14) - Payroll claim: OT ID 32 (Dec 25 Christmas - public_holiday)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(32),
 'payroll', 0.00, 4.00, 0.00, 'approved', 'December Christmas public holiday OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS416 (staff_id 16) - Payroll claim: OT ID 33 (Dec 26)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(33),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS417 (staff_id 17) - Payroll claim: OT ID 34 (Dec 28)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(34),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS419 (staff_id 19) - Payroll claim: OT ID 35 (Dec 3)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(35),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS420 (staff_id 20) - Payroll claim: OT ID 36 (Dec 7)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(36),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS422 (staff_id 22) - Payroll claim: OT ID 37 (Dec 11)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(37),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- YS423 (staff_id 23) - Payroll claim: OT ID 38 (Dec 14)
INSERT INTO `ot_claims` (`ot_ids`, `claim_type`, `fulltime_hours`, `public_holiday_hours`, `replacement_days`, `status`, `remarks`, `created_at`, `updated_at`)
VALUES
(JSON_ARRAY(38),
 'payroll', 4.00, 0.00, 0.00, 'approved', 'December fulltime OT for payroll processing', '2025-12-31', '2025-12-31');

-- =========================================================
-- MARK ALL CLAIMED OVERTIMES AS claimed = 1
-- This ensures each overtime is tracked as used
-- Prevents the same OT record from being claimed again
-- =========================================================

-- Mark replacement leave claimed overtimes (OT IDs 2, 4, 5, 7, 11, 12, 14, 16, 17, 19, 23, 24 - excluding 1, 3, 6, 8, 9, 10, 13, 15, 18, 20, 21, 22 for converted staff)
UPDATE `overtimes` SET `claimed` = 1 WHERE `id` IN (2, 4, 5, 7, 11, 12, 14, 16, 17, 19, 23, 24);

-- Mark payroll claimed overtimes (OT IDs 1, 3, 6, 8, 9, 10, 13, 15, 18, 20, 21, 22, 25-38)
UPDATE `overtimes` SET `claimed` = 1 WHERE `id` IN (1, 3, 6, 8, 9, 10, 13, 15, 18, 20, 21, 22, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 38);

-- =========================================================
-- VALIDATION: Verify no duplicate OT IDs across all claims
-- =========================================================
-- Total OT IDs claimed: 36 unique IDs (October-December 2025)
-- - Replacement leave claims: IDs 2, 4, 5, 7, 11, 12, 14, 16, 17, 19, 23, 24 (6 claims, Oct-Nov pairs)
--   Staff IDs: 4, 8, 10, 13, 19, 20
--   Each claim: 2 OT records from same staff, 8 hours = 1 day
--
-- - Payroll claims: IDs 1, 3, 6, 8, 9, 10, 13, 15, 18, 20, 21, 22, 25-38 (24 claims, Oct-Dec)
--   Converted from replacement leave (Oct-Nov): Staff 3, 6, 11, 16, 17 (10 OT IDs: 1, 3, 6, 9, 10, 13, 15, 18, 21, 22)
--   Other payroll claims (Dec): Staff 3, 4, 6, 8, 10, 11, 13, 14, 16, 17, 19, 20, 22, 23 (14 OT IDs: 25-38)
--   Staff 14 special payroll: OT IDs 8, 20 (Oct-Nov 4 PH + 4 FT) and 32 (Dec 25 Christmas PH)
--
-- - Zero overlap between claim types
-- - All 36 claimed overtimes marked with claimed=1
--
-- VALIDATION QUERY:
-- SELECT COUNT(*) as total_claimed FROM overtimes WHERE claimed=1;
-- Expected: 36 rows
-- =========================================================

-- Additional Verification:
-- SELECT claim_type, COUNT(*) as claim_count, COUNT(DISTINCT JSON_EXTRACT(ot_ids, '$[0]')) as unique_ot_ids FROM ot_claims GROUP BY claim_type;
-- Expected: replacement_leave: 12 claims, 12 unique starting IDs; payroll: 14 claims, 14 unique OT IDs

-- =========================================================
-- END OF OT CLAIMS SEED DATA
-- =========================================================
