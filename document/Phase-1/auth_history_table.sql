-- Exploria Travel and Tours - Authentication History Table
-- This table stores every login/authentication event for security and analytics

CREATE TABLE `auth_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `auth_refid` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique identifier format: AUT-DDMMYYYYHHMMSS-XXX',
  `user_refid` VARCHAR(50) NOT NULL COMMENT 'Reference to users table',
  `firebase_uid` VARCHAR(255) DEFAULT NULL COMMENT 'Firebase User ID',
  
  -- Authentication Details
  `auth_method` ENUM('email_password', 'google', 'facebook', 'apple', 'phone', 'anonymous') NOT NULL COMMENT 'Authentication method used',
  `auth_status` ENUM('success', 'failed', 'blocked', 'suspicious') NOT NULL DEFAULT 'success',
  `auth_type` ENUM('login', 'logout', 'refresh', 'password_reset', 'email_verification') NOT NULL DEFAULT 'login',
  `session_token` VARCHAR(255) DEFAULT NULL COMMENT 'Session or auth token (hashed)',
  `session_duration` INT DEFAULT NULL COMMENT 'Session duration in minutes',
  
  -- Device Information
  `device_type` VARCHAR(50) DEFAULT NULL COMMENT 'mobile, web, tablet, desktop',
  `device_model` VARCHAR(100) DEFAULT NULL COMMENT 'Device model/brand',
  `device_id` VARCHAR(255) DEFAULT NULL COMMENT 'Unique device identifier (hashed)',
  `os_version` VARCHAR(50) DEFAULT NULL COMMENT 'Operating system and version',
  `app_version` VARCHAR(20) DEFAULT NULL COMMENT 'Application version',
  `browser` VARCHAR(100) DEFAULT NULL COMMENT 'Browser name and version',
  `user_agent` TEXT DEFAULT NULL COMMENT 'Full user agent string',
  
  -- Location Information
  `ip_address` VARCHAR(45) DEFAULT NULL COMMENT 'IP address (IPv4 or IPv6)',
  `gps_location` POINT DEFAULT NULL COMMENT 'GPS coordinates at login',
  `country` VARCHAR(100) DEFAULT NULL COMMENT 'Country from IP/GPS',
  `city` VARCHAR(100) DEFAULT NULL COMMENT 'City from IP/GPS',
  `region` VARCHAR(100) DEFAULT NULL COMMENT 'State/Region from IP/GPS',
  `timezone` VARCHAR(50) DEFAULT NULL COMMENT 'User timezone',
  `isp` VARCHAR(255) DEFAULT NULL COMMENT 'Internet Service Provider',
  
  -- Security Flags
  `is_suspicious` TINYINT(1) DEFAULT 0 COMMENT 'Flagged as suspicious activity',
  `is_new_device` TINYINT(1) DEFAULT 0 COMMENT 'Login from new device',
  `is_new_location` TINYINT(1) DEFAULT 0 COMMENT 'Login from new location',
  `requires_verification` TINYINT(1) DEFAULT 0 COMMENT 'Requires additional verification',
  `two_factor_used` TINYINT(1) DEFAULT 0 COMMENT 'Two-factor authentication used',
  `biometric_used` TINYINT(1) DEFAULT 0 COMMENT 'Biometric authentication used',
  
  -- Failure Information (for failed login attempts)
  `failure_reason` VARCHAR(255) DEFAULT NULL COMMENT 'Reason for failed authentication',
  `attempt_count` INT DEFAULT 1 COMMENT 'Number of attempts for this session',
  
  -- Additional Metadata
  `referrer_url` TEXT DEFAULT NULL COMMENT 'Referring URL',
  `login_page` VARCHAR(255) DEFAULT NULL COMMENT 'Page where login occurred',
  `notes` TEXT DEFAULT NULL COMMENT 'Additional notes or metadata',
  `risk_score` DECIMAL(3,2) DEFAULT 0.00 COMMENT 'Calculated risk score 0.00-1.00',
  
  -- Timestamps
  `auth_timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When authentication occurred',
  `logout_timestamp` TIMESTAMP NULL DEFAULT NULL COMMENT 'When user logged out',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  PRIMARY KEY (`id`),
  INDEX `idx_auth_refid` (`auth_refid`),
  INDEX `idx_user_refid` (`user_refid`),
  INDEX `idx_firebase_uid` (`firebase_uid`),
  INDEX `idx_auth_method` (`auth_method`),
  INDEX `idx_auth_status` (`auth_status`),
  INDEX `idx_auth_type` (`auth_type`),
  INDEX `idx_auth_timestamp` (`auth_timestamp`),
  INDEX `idx_ip_address` (`ip_address`),
  INDEX `idx_device_type` (`device_type`),
  INDEX `idx_is_suspicious` (`is_suspicious`),
  INDEX `idx_is_new_device` (`is_new_device`),
  INDEX `idx_is_new_location` (`is_new_location`),
  INDEX `idx_created_at` (`created_at`),
  SPATIAL INDEX `idx_gps_location` (`gps_location`),
  FOREIGN KEY (`user_refid`) REFERENCES `users` (`user_refid`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Authentication history for security tracking and analytics';

-- Create indexes for performance optimization on large datasets
CREATE INDEX `idx_user_auth_timestamp` ON `auth_history` (`user_refid`, `auth_timestamp`);
CREATE INDEX `idx_user_auth_status` ON `auth_history` (`user_refid`, `auth_status`);
CREATE INDEX `idx_date_range` ON `auth_history` (`auth_timestamp`, `auth_status`);
