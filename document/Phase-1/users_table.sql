-- Exploria Travel and Tours - Users Table
-- This table stores user information after Firebase authentication

CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_refid` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique identifier format: USR-DDMMYYYYHHMMSS-XXX',
  `firebase_uid` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Firebase User ID',
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `email_verified` TINYINT(1) DEFAULT 0 COMMENT '0=not verified, 1=verified',
  `firstname` VARCHAR(100) DEFAULT NULL,
  `lastname` VARCHAR(100) DEFAULT NULL,
  `display_name` VARCHAR(200) DEFAULT NULL COMMENT 'Full name or preferred display name',
  `mobile_number` VARCHAR(20) DEFAULT NULL,
  `mobile_country_code` VARCHAR(5) DEFAULT NULL COMMENT 'Country code e.g., +63, +1',
  `birthday` DATE DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other', 'prefer_not_to_say') DEFAULT NULL,
  `profile_photo_url` TEXT DEFAULT NULL COMMENT 'URL to profile photo',
  
  -- User Status and Roles
  `confirmed` TINYINT(1) DEFAULT 0 COMMENT '0=new user with incomplete info, 1=user with complete info',
  `is_operator` TINYINT(1) DEFAULT 0 COMMENT '0=regular user, 1=tour operator/business partner',
  `is_admin` TINYINT(1) DEFAULT 0 COMMENT '0=regular user, 1=administrator',
  `is_staff` TINYINT(1) DEFAULT 0 COMMENT '0=regular user, 1=staff member',
  `account_status` ENUM('active', 'suspended', 'inactive', 'deleted') DEFAULT 'active',
  
  -- Travel Platform Specific Fields
  `preferred_language` VARCHAR(10) DEFAULT 'en' COMMENT 'ISO language code',
  `preferred_currency` VARCHAR(5) DEFAULT 'USD' COMMENT 'ISO currency code',
  `home_country` VARCHAR(100) DEFAULT NULL COMMENT 'User home country',
  `home_city` VARCHAR(100) DEFAULT NULL COMMENT 'User home city',
  `nationality` VARCHAR(100) DEFAULT NULL,
  `passport_number` VARCHAR(50) DEFAULT NULL COMMENT 'Encrypted passport information',
  `passport_expiry` DATE DEFAULT NULL,
  `travel_preferences` JSON DEFAULT NULL COMMENT 'JSON object storing travel interests, preferences',
  `loyalty_points` INT DEFAULT 0 COMMENT 'Accumulated loyalty/reward points',
  `member_tier` ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze',
  
  -- Location Tracking
  `gps_live` POINT DEFAULT NULL COMMENT 'Current GPS coordinates',
  `last_known_location` VARCHAR(255) DEFAULT NULL COMMENT 'Last known location name',
  `timezone` VARCHAR(50) DEFAULT NULL COMMENT 'User timezone',
  
  -- Device Information
  `last_login_device` VARCHAR(50) DEFAULT NULL COMMENT 'Device type: mobile, web, tablet',
  `last_login_ip` VARCHAR(45) DEFAULT NULL COMMENT 'Last login IP address',
  `device_token` TEXT DEFAULT NULL COMMENT 'Push notification device token',
  
  -- Privacy and Communication
  `newsletter_subscribed` TINYINT(1) DEFAULT 1 COMMENT 'Newsletter subscription status',
  `marketing_consent` TINYINT(1) DEFAULT 0 COMMENT 'Marketing communication consent',
  `data_sharing_consent` TINYINT(1) DEFAULT 0 COMMENT 'Data sharing consent with partners',
  `privacy_settings` JSON DEFAULT NULL COMMENT 'User privacy preferences',
  
  -- Emergency Contact (for travelers)
  `emergency_contact_name` VARCHAR(200) DEFAULT NULL,
  `emergency_contact_phone` VARCHAR(20) DEFAULT NULL,
  `emergency_contact_relationship` VARCHAR(50) DEFAULT NULL,
  
  -- Verification and Security
  `two_factor_enabled` TINYINT(1) DEFAULT 0 COMMENT 'Two-factor authentication status',
  `kyc_verified` TINYINT(1) DEFAULT 0 COMMENT 'Know Your Customer verification for operators',
  `kyc_document_url` TEXT DEFAULT NULL COMMENT 'KYC document storage URL',
  `verification_date` DATETIME DEFAULT NULL COMMENT 'Date when account was verified',
  
  -- Metadata
  `notes` TEXT DEFAULT NULL COMMENT 'Admin notes about user',
  `referral_code` VARCHAR(20) UNIQUE DEFAULT NULL COMMENT 'User referral code',
  `referred_by` VARCHAR(50) DEFAULT NULL COMMENT 'user_refid of referring user',
  `registration_source` VARCHAR(50) DEFAULT NULL COMMENT 'web, mobile_ios, mobile_android, etc.',
  
  -- Timestamps
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'Soft delete timestamp',
  
  PRIMARY KEY (`id`),
  INDEX `idx_user_refid` (`user_refid`),
  INDEX `idx_firebase_uid` (`firebase_uid`),
  INDEX `idx_email` (`email`),
  INDEX `idx_mobile` (`mobile_number`),
  INDEX `idx_confirmed` (`confirmed`),
  INDEX `idx_is_operator` (`is_operator`),
  INDEX `idx_is_admin` (`is_admin`),
  INDEX `idx_is_staff` (`is_staff`),
  INDEX `idx_account_status` (`account_status`),
  INDEX `idx_created_at` (`created_at`),
  INDEX `idx_last_login` (`last_login_at`),
  SPATIAL INDEX `idx_gps_live` (`gps_live`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Users table for Exploria Travel and Tours platform';
