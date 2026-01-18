-- =========================================================
-- SHIFTS SEED (NO REST DAY ON SATURDAY OR SUNDAY)
-- Rule you requested:
-- 1) Every staff MUST have exactly 1 rest day per week (Mon–Fri only)
-- 2) Saturday & Sunday are NEVER rest days
-- 3) Rest day rows use: start_time='', end_time='', break_minutes=0, rest_day=1
-- =========================================================


-- =========================================================
-- OCT 1-5 2025 (Wed→Sun)
-- (This is a partial week window, so some staff rest day is inside Oct 1-3 only,
-- and everyone still works Sat/Sun because rest day cannot be Sat/Sun.)
-- =========================================================
INSERT INTO `shifts`
(staff_id, `date`, day_of_week, start_time, end_time, break_minutes, rest_day, created_at, updated_at)
VALUES
-- Manager (YS401) - works every day 10:00-23:00 break 60 (NO rest in partial window)
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-01', 'wed', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-02', 'thu', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-03', 'fri', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-04', 'sat', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-05', 'sun', '10:00', '23:00', 60, 0, NOW(), NOW()),

-- Supervisor (YS402) - works every day 07:00-23:00 break 240 (NO rest in partial window)
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-01', 'wed', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-02', 'thu', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-03', 'fri', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-04', 'sat', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-05', 'sun', '07:00', '23:00', 240, 0, NOW(), NOW()),

-- Cashiers (YS403..YS405) rest days on Wed/Thu/Fri (Mon–Fri only OK)
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-01', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-02', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-03', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Baristas (YS406..YS407) - rest days MUST NOT be Sat/Sun
-- Change: YS406 rest Thu (instead of Sat)
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-01', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

-- Change: YS407 rest Fri (instead of Sun)
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-03', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Jokis (YS408..YS409) - OK (rest Wed/Thu)
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-01', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-02', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-03', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Waiters (YS410..YS418) - move any Sat/Sun rest to Mon–Fri
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-01', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-02', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-03', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Change: YS413 rest Wed (instead of Sat)
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-01', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-02', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

-- Change: YS414 rest Thu (instead of Sun)
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

-- YS415..YS418 keep working all days in this partial window
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-03', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-01', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-02', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-02', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-03', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Kitchen (YS419..YS424) - move any Sat/Sun rest to Mon–Fri
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-01', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-02', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-03', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-03', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Change: YS422 rest Thu (instead of Sat)
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-01', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-02', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-03', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-04', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-05', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

-- Change: YS423 rest Fri (instead of Sun)
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-01', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-02', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-03', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-04', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-05', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

-- YS424 works all days in this partial window (rest will be in full week below)
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-01', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-02', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-03', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-04', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-05', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW());


-- =========================================================
-- WEEK 1 (2025-10-06 to 2025-10-12)
-- Every staff gets EXACTLY ONE rest day (Mon–Fri only).
-- No rest day on Sat/Sun for anyone.
-- =========================================================
INSERT INTO `shifts`
(staff_id, `date`, day_of_week, start_time, end_time, break_minutes, rest_day, created_at, updated_at)
VALUES
-- Manager (YS401) - rest Mon
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-07', 'tue', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-08', 'wed', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-09', 'thu', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-10', 'fri', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-11', 'sat', '10:00', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS401'), '2025-10-12', 'sun', '10:00', '23:00', 60, 0, NOW(), NOW()),

-- Supervisor (YS402) - rest Tue
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-06', 'mon', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-08', 'wed', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-09', 'thu', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-10', 'fri', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-11', 'sat', '07:00', '23:00', 240, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS402'), '2025-10-12', 'sun', '07:00', '23:00', 240, 0, NOW(), NOW()),

-- Cashiers (YS403 rest Mon; YS404 rest Tue; YS405 rest Wed)
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-09', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS403'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-10', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS404'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-08', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS405'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Baristas (YS406 rest Thu; YS407 rest Fri)  (NO Sun rest anymore)
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-06', 'mon', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-09', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS406'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-08', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-10', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS407'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Jokis (YS408 rest Fri; YS409 rest Mon) (NO Sat rest)
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-07', 'tue', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-10', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS408'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-08', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS409'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Waiters (YS410..YS418) - each has exactly ONE rest day Mon–Fri
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-09', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS410'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-10', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS411'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-08', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS412'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-06', 'mon', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-09', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS413'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-07', 'tue', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-10', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS414'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

-- Change: YS415 rest Mon (instead of Sat)
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-08', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS415'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Change: YS416 rest Tue (instead of Sun)
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-06', 'mon', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-09', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS416'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-07', 'tue', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-10', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS417'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-08', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS418'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

-- Kitchen (YS419..YS424) - each exactly ONE rest day Mon–Fri, no Sat rest
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-06', 'mon', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-09', 'thu', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS419'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-07', 'tue', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-10', 'fri', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS420'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-08', 'wed', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-09', 'thu', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS421'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-06', 'mon', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-07', 'tue', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-08', 'wed', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-09', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-10', 'fri', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-11', 'sat', '06:00', '14:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS422'), '2025-10-12', 'sun', '06:00', '14:30', 60, 0, NOW(), NOW()),

((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-06', 'mon', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-07', 'tue', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-08', 'wed', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-09', 'thu', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-10', 'fri', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-11', 'sat', '09:00', '17:30', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS423'), '2025-10-12', 'sun', '09:00', '17:30', 60, 0, NOW(), NOW()),

-- Change: YS424 rest Thu (instead of Sat)
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-06', 'mon', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-07', 'tue', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-08', 'wed', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-09', 'thu', '', '', 0, 1, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-10', 'fri', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-11', 'sat', '14:30', '23:00', 60, 0, NOW(), NOW()),
((SELECT id FROM staff WHERE employee_id='YS424'), '2025-10-12', 'sun', '14:30', '23:00', 60, 0, NOW(), NOW());