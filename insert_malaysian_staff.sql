-- SQL Statements to Insert Malaysian Staff Users
-- Total: 24 staff members
-- All hire dates: 1 October 2025
-- 
-- PASSWORD FOR ALL STAFF: 123456789
-- (Same password as used in AdminSeeder and StaffSeeder)
-- All users have the password hash for '123456789'

-- ============================================
-- INSERT INTO USERS TABLE
-- ============================================

-- Manager (1)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Ahmad bin Abdullah', 'ahmad.abdullah@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456789', '123 Jalan Bukit Bintang, Kuala Lumpur, 50000', 1, NOW(), NOW());

-- Supervisor (1)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Siti Nurhaliza binti Mohd', 'siti.nurhaliza@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456790', '456 Jalan Ampang, Kuala Lumpur, 50450', 1, NOW(), NOW());

-- Cashier (3)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Lim Wei Ming', 'lim.weiming@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456791', '789 Jalan Pudu, Kuala Lumpur, 50050', 1, NOW(), NOW()),
('Nurul Aina binti Hassan', 'nurul.aina@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456792', '321 Jalan Tun Razak, Kuala Lumpur, 50400', 1, NOW(), NOW()),
('Tan Mei Ling', 'tan.meiling@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456793', '654 Jalan Imbi, Kuala Lumpur, 55100', 1, NOW(), NOW());

-- Barista (2)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Muhammad Firdaus bin Ismail', 'firdaus.ismail@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456794', '987 Jalan Bangsar, Kuala Lumpur, 59000', 1, NOW(), NOW()),
('Chong Yee Leng', 'chong.yeeleng@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456795', '147 Jalan SS2, Petaling Jaya, 47300', 1, NOW(), NOW());

-- Joki (2)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Razak bin Osman', 'razak.osman@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456796', '258 Jalan Klang Lama, Kuala Lumpur, 58000', 1, NOW(), NOW()),
('Lee Chee Keong', 'lee.cheekeong@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456797', '369 Jalan Cheras, Kuala Lumpur, 56000', 1, NOW(), NOW());

-- Waiter (9)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Nur Fatimah binti Ahmad', 'nur.fatimah@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456798', '741 Jalan Masjid India, Kuala Lumpur, 50050', 1, NOW(), NOW()),
('Wong Siew Leng', 'wong.siewleng@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456799', '852 Jalan Tuanku Abdul Rahman, Kuala Lumpur, 50100', 1, NOW(), NOW()),
('Aminah binti Yusof', 'aminah.yusof@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456800', '963 Jalan Raja Chulan, Kuala Lumpur, 50200', 1, NOW(), NOW()),
('Goh Boon Heng', 'goh.boonheng@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456801', '159 Jalan Sultan Ismail, Kuala Lumpur, 50250', 1, NOW(), NOW()),
('Zainab binti Mohd Ali', 'zainab.mohdali@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456802', '357 Jalan Bukit Ceylon, Kuala Lumpur, 50200', 1, NOW(), NOW()),
('Teo Kian Huat', 'teo.kianhuat@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456803', '468 Jalan Dang Wangi, Kuala Lumpur, 50100', 1, NOW(), NOW()),
('Norazlina binti Hashim', 'norazlina.hashim@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456804', '579 Jalan Conlay, Kuala Lumpur, 50450', 1, NOW(), NOW()),
('Ng Chee Wah', 'ng.cheewah@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456805', '680 Jalan Raja Laut, Kuala Lumpur, 50350', 1, NOW(), NOW()),
('Salmah binti Zainal', 'salmah.zainal@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456806', '791 Jalan Perak, Kuala Lumpur, 50450', 1, NOW(), NOW());

-- Kitchen (6)
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Hassan bin Mohd Noor', 'hassan.mohdnoor@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456807', '135 Jalan Kuchai Lama, Kuala Lumpur, 58200', 1, NOW(), NOW()),
('Lau Kim Chuan', 'lau.kimchuan@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456808', '246 Jalan Pasar, Kuala Lumpur, 50050', 1, NOW(), NOW()),
('Rosli bin Ahmad', 'rosli.ahmad@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456809', '357 Jalan Petaling, Kuala Lumpur, 50000', 1, NOW(), NOW()),
('Ooi Beng Chye', 'ooi.bengchye@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456810', '468 Jalan Brickfields, Kuala Lumpur, 50470', 1, NOW(), NOW()),
('Azman bin Mat', 'azman.mat@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456811', '579 Jalan Sentul, Kuala Lumpur, 51000', 1, NOW(), NOW()),
('Yap Seng Chai', 'yap.sengchai@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456812', '680 Jalan Segambut, Kuala Lumpur, 51200', 1, NOW(), NOW());

-- ============================================
-- INSERT INTO STAFF TABLE
-- Note: user_id values are linked via email subqueries (no need to adjust IDs)
-- Employee IDs start from YS401 and increment sequentially (YS401 to YS424)
-- All hire dates: 1 October 2025 (2025-10-01)
-- Department distribution matches Staff model limits:
--   Manager: 1, Supervisor: 1, Cashier: 3, Barista: 2, Joki: 2, Waiter: 9, Kitchen: 6
-- Salaries follow StaffRegisterController::getDefaultSalary() method:
--   Manager: 4000.00, Supervisor: 3000.00, Cashier: 2000.00, Barista: 1800.00,
--   Joki: 1600.00, Waiter: 1500.00, Kitchen: 1800.00
-- ============================================

-- Manager (1) - Employee IDs start from YS401
-- Salary follows StaffRegisterController::getDefaultSalary() - manager: 4000.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'ahmad.abdullah@gmail.com'), 'YS401', 'manager', '2025-10-01', 4000.00, 'active', NOW(), NOW());

-- Supervisor (1)
-- Salary follows StaffRegisterController::getDefaultSalary() - supervisor: 3000.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'siti.nurhaliza@gmail.com'), 'YS402', 'supervisor', '2025-10-01', 3000.00, 'active', NOW(), NOW());

-- Cashier (3)
-- Salary follows StaffRegisterController::getDefaultSalary() - cashier: 2000.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'lim.weiming@gmail.com'), 'YS403', 'cashier', '2025-10-01', 2000.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'nurul.aina@gmail.com'), 'YS404', 'cashier', '2025-10-01', 2000.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'tan.meiling@gmail.com'), 'YS405', 'cashier', '2025-10-01', 2000.00, 'active', NOW(), NOW());

-- Barista (2)
-- Salary follows StaffRegisterController::getDefaultSalary() - barista: 1800.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'firdaus.ismail@gmail.com'), 'YS406', 'barista', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'chong.yeeleng@gmail.com'), 'YS407', 'barista', '2025-10-01', 1800.00, 'active', NOW(), NOW());

-- Joki (2)
-- Salary follows StaffRegisterController::getDefaultSalary() - joki: 1600.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'razak.osman@gmail.com'), 'YS408', 'joki', '2025-10-01', 1600.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'lee.cheekeong@gmail.com'), 'YS409', 'joki', '2025-10-01', 1600.00, 'active', NOW(), NOW());

-- Waiter (9)
-- Salary follows StaffRegisterController::getDefaultSalary() - waiter: 1500.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'nur.fatimah@gmail.com'), 'YS410', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'wong.siewleng@gmail.com'), 'YS411', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'aminah.yusof@gmail.com'), 'YS412', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'goh.boonheng@gmail.com'), 'YS413', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'zainab.mohdali@gmail.com'), 'YS414', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'teo.kianhuat@gmail.com'), 'YS415', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'norazlina.hashim@gmail.com'), 'YS416', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'ng.cheewah@gmail.com'), 'YS417', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'salmah.zainal@gmail.com'), 'YS418', 'waiter', '2025-10-01', 1500.00, 'active', NOW(), NOW());

-- Kitchen (6)
-- Salary follows StaffRegisterController::getDefaultSalary() - kitchen: 1800.00
INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'hassan.mohdnoor@gmail.com'), 'YS419', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'lau.kimchuan@gmail.com'), 'YS420', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'rosli.ahmad@gmail.com'), 'YS421', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'ooi.bengchye@gmail.com'), 'YS422', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'azman.mat@gmail.com'), 'YS423', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW()),
((SELECT id FROM users WHERE email = 'yap.sengchai@gmail.com'), 'YS424', 'kitchen', '2025-10-01', 1800.00, 'active', NOW(), NOW());

-- ============================================
-- ADDITIONAL STAFF (Ordered by Hire Date)
-- ============================================

-- Supervisor (1) - hire date: 15 October 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Rajesh Kumar', 'rajesh.kumar@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456813', '456 Jalan Federal, Kuala Lumpur, 50000', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'rajesh.kumar@gmail.com'), 'YS425', 'supervisor', '2025-10-15', 3000.00, 'active', NOW(), NOW());

-- Barista (1) - hire date: 15 October 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Priya Sharma', 'priya.sharma@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456814', '321 Jalan Merdeka, Kuala Lumpur, 50000', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'priya.sharma@gmail.com'), 'YS426', 'barista', '2025-10-15', 1800.00, 'active', NOW(), NOW());

-- Joki (1) - hire date: 15 November 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Vikram Singh', 'vikram.singh@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456815', '789 Jalan Lembah Perdana, Kuala Lumpur, 50200', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'vikram.singh@gmail.com'), 'YS427', 'joki', '2025-11-15', 1600.00, 'active', NOW(), NOW());

-- Kitchen (1) - hire date: 01 December 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Arun Patel', 'arun.patel@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456818', '357 Jalan Ara, Kuala Lumpur, 55100', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'arun.patel@gmail.com'), 'YS428', 'kitchen', '2025-12-01', 1800.00, 'active', NOW(), NOW());

-- Joki (1) - hire date: 15 December 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Suresh Nair', 'suresh.nair@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456816', '654 Jalan Semantan, Kuala Lumpur, 58100', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'suresh.nair@gmail.com'), 'YS429', 'joki', '2025-12-15', 1600.00, 'active', NOW(), NOW());

-- Waiter (1) - hire date: 15 December 2025
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Anjali Desai', 'anjali.desai@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456817', '951 Jalan Ceri, Kuala Lumpur, 53100', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'anjali.desai@gmail.com'), 'YS430', 'waiter', '2025-12-15', 1500.00, 'active', NOW(), NOW());

-- Cashier (1) - hire date: 01 January 2026
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Divya Pillai', 'divya.pillai@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456819', '789 Jalan Sulong, Kuala Lumpur, 55000', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'divya.pillai@gmail.com'), 'YS431', 'cashier', '2026-01-01', 2000.00, 'active', NOW(), NOW());

-- Barista (1) - hire date: 01 January 2026
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Meera Gupta', 'meera.gupta@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456820', '147 Jalan Permata, Kuala Lumpur, 54000', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'meera.gupta@gmail.com'), 'YS432', 'barista', '2026-01-01', 1800.00, 'active', NOW(), NOW());

-- Kitchen (1) - hire date: 01 January 2026
INSERT INTO `users` (`name`, `email`, `password`, `role`, `phone`, `address`, `first_login`, `created_at`, `updated_at`) VALUES
('Nikhil Reddy', 'nikhil.reddy@gmail.com', '$2y$12$oJVdFMiDi/h1VngC/6TA2uSF1Ov3PkGGPVnUuCN20mh1Il9xLxkuS', 'staff', '+60123456821', '258 Jalan Flora, Kuala Lumpur, 55100', 1, NOW(), NOW());

INSERT INTO `staff` (`user_id`, `employee_id`, `department`, `hire_date`, `salary`, `status`, `created_at`, `updated_at`) VALUES
((SELECT id FROM users WHERE email = 'nikhil.reddy@gmail.com'), 'YS433', 'kitchen', '2026-01-01', 1800.00, 'active', NOW(), NOW());

