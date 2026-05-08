-- RSYNC Applications Table
-- Copy and paste this entire query into phpMyAdmin SQL tab to create the applications table

CREATE TABLE IF NOT EXISTS `applications` (
  `application_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `room_id` int NOT NULL,
  `status` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `applied_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`application_id`),
  UNIQUE KEY `unique_application` (`user_id`,`room_id`),
  KEY `idx_room_id` (`room_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
