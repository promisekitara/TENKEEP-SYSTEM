CREATE DATABASE tenkeep_db;
USE `tenkeep_db`;

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `timestamp` datetime DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL,
  `details` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `complaint_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `complaint_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `subject` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `reply` text DEFAULT NULL,
  `reply_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`complaint_id`, `tenant_id`, `property_id`, `complaint_date`, `subject`, `message`, `reply`, `reply_date`) VALUES
(1, 1, 1, '2025-05-14 10:16:34', 'Noise', 'Mr. Patrick plays very loud music in the night. warn him', 'Sorry Ajok. I will personally handle the issue. worry not\r\n', '2025-05-27 05:24:44'),
(2, 2, 2, '2025-05-15 15:59:15', 'Theft', 'I think Mr. Okello stole my shoes from my doorstep', NULL, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `paypal_transaction_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `tenant_id`, `property_id`, `payment_date`, `amount`, `description`, `paypal_transaction_id`) VALUES
(1, 1, 1, '2025-05-29', 250000.00, 'Paid her monthly fee', NULL),
(2, 2, 2, '2025-05-02', 200.00, 'She paid in full', NULL),
(3, 3, 1, '2025-05-16', 1000.00, 'She plans to push on for about 3 months. promised to complete balance later', NULL),
(4, 4, 4, '2025-05-29', 800.00, 'This covers 2 months', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `properties`
--

CREATE TABLE `properties` (
  `property_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'USD',
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `properties`
--

INSERT INTO `properties` (`property_id`, `owner_id`, `name`, `address`, `price`, `currency`, `image_path`) VALUES
(1, 1, 'LaRasa', 'Pece Acoyo, Gulu', 250000.00, 'USD', NULL),
(2, 3, 'Lajok Entity', 'Buyemba, Tororo', 200.00, 'USD', NULL),
(3, 5, 'Tennessy Estates', 'Florida, USA', 500.00, 'USD', NULL),
(4, 1, 'PINES CAGE', 'Pece, Laroo Division', 400.00, 'USD', NULL),
(5, 7, 'Swiss Estates', 'Masaka, Uganda', 250.00, 'USD', NULL),
(6, 7, 'Sana Guest House', 'Pece, Laroo', 200000.00, 'UGX', NULL),
(7, 7, 'Giramia Houses', 'Bar Dege, Gulu', 500000.00, 'UGX', ''),
(8, 7, 'Timeless Properties', 'Ntinda, Kampala', 780000.00, 'UGX', '');

-- --------------------------------------------------------

--
-- Table structure for table `recurring_charges`
--

CREATE TABLE `recurring_charges` (
  `charge_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `tenant_id` int(11) NOT NULL,
  `charge_type` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `frequency` enum('monthly','quarterly','annually') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `due_day` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tenants`
--

CREATE TABLE `tenants` (
  `tenant_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `property_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tenants`
--

INSERT INTO `tenants` (`tenant_id`, `user_id`, `property_id`, `name`, `contact_number`) VALUES
(1, 2, 1, 'Ajok', '0781259927'),
(2, 4, 2, 'Waniyo Peace', '1234567898'),
(3, 6, 1, 'Mercy Achen', '0786830788'),
(4, 8, 4, 'Samantha Cheeks', '+123432455654');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('owner','tenant','developer') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `role`) VALUES
(1, 'PromiseK', '$2y$10$a5jUIo7L//XtJoJ9nOcEYuvDuCyb/clNhXAYrCs8kawc4lDh4v8lS', 'owner'),
(2, 'AjokLarasa', '$2y$10$Xs2JFCdv1uXak1d5UwFvQe.k9fUl8pbhbz1Yhvw3CNWmdW0ecmcjm', 'tenant'),
(3, 'david@gmail.com', '$2y$10$DScKOwDHjP/SdN.WB8CbBurU6jg.JfOjfxlhqBDMh9jAMqteY3TUO', 'owner'),
(4, 'wan@gmail.com', '$2y$10$vulUKfx1m1YFmookgpdeDeirghhjU5NQXEgi4KJ..9Wj9hIiwzUZC', 'tenant'),
(5, 'Denis', '$2y$10$mwkk7glNm0Jh0TrPMmX72OFFJbLgBDv0zveYV92ngkixb7fuT.TX.', 'owner'),
(6, 'MercyAchen', '$2y$10$q6jWtOSmzd65yEJJR1ndUOhzWTKSr4zeR208k8HQ.fJH3EQZ6xnEG', 'tenant'),
(7, 'Atim Precious', '$2y$10$5qDFPcHkGya1yzNSQFcObOusaDuqishAMGFGqe8S9gs2YSf1WhFKa', 'owner'),
(8, 'Samantha Cheeks', '$2y$10$c1MI.3RlMd8VDlTz.WVxYeJk3BDDWz.oFb0GD8D53MQmwgd.blzxm', 'tenant'),
(20, 'Promise Kitara', '$2y$10$QwQwQwQwQwQwQwQwQwQwQeQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQwQw', 'developer')
ON DUPLICATE KEY UPDATE username=username;

-- --------------------------------------------------------

--
-- Table structure for table `developers`
--

CREATE TABLE `developers` (
  `developer_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `access_level` varchar(50) DEFAULT 'full',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`developer_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `developers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `paypal_transaction_id` (`paypal_transaction_id`),
  ADD KEY `tenant_id` (`tenant_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `properties`
--
ALTER TABLE `properties`
  ADD PRIMARY KEY (`property_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `recurring_charges`
--
ALTER TABLE `recurring_charges`
  ADD PRIMARY KEY (`charge_id`),
  ADD KEY `property_id` (`property_id`),
  ADD KEY `tenant_id` (`tenant_id`);

--
-- Indexes for table `tenants`
--
ALTER TABLE `tenants`
  ADD PRIMARY KEY (`tenant_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `property_id` (`property_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `developers`
--
ALTER TABLE `developers`
  ADD PRIMARY KEY (`developer_id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `properties`
--
ALTER TABLE `properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `recurring_charges`
--
ALTER TABLE `recurring_charges`
  MODIFY `charge_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tenants`
--
ALTER TABLE `tenants`
  MODIFY `tenant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `developers`
--
ALTER TABLE `developers`
  MODIFY `developer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaints_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  ADD CONSTRAINT `complaints_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`);

--
-- Constraints for table `properties`
--
ALTER TABLE `properties`
  ADD CONSTRAINT `properties_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `recurring_charges`
--
ALTER TABLE `recurring_charges`
  ADD CONSTRAINT `recurring_charges_ibfk_1` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`),
  ADD CONSTRAINT `recurring_charges_ibfk_2` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`tenant_id`);

--
-- Constraints for table `tenants`
--
ALTER TABLE `tenants`
  ADD CONSTRAINT `tenants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `tenants_ibfk_2` FOREIGN KEY (`property_id`) REFERENCES `properties` (`property_id`);

--
-- Constraints for table `developers`
--
ALTER TABLE `developers`
  ADD CONSTRAINT `developers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
