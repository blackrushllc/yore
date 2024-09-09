# CHS App
A Yore Plugin

+ Does stuff for the CHS domain
+ 
```
CREATE TABLE `chs_rooms` (
  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `status` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
  `unit` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `room` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `alarm` VARCHAR(50) NULL DEFAULT NULL COMMENT 'supplies,cleaning,nurse,urgent,clear' COLLATE 'utf8mb4_unicode_ci',
  `code` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
  `created_at` DATETIME NULL DEFAULT 'CURRENT_TIMESTAMP',
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
  )
  COLLATE='utf8mb4_unicode_ci'
  ENGINE=InnoDB
  ;
  
  CREATE TABLE `chs_users` (
	`id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
	`status` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
	`hospital_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`hospital_address` TEXT NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`primary_contact_name` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`primary_contact_phone` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`primary_contact_email` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`code` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`admin_username` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`admin_password` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`console_username` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`console_password` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci',
	`created_at` DATETIME NULL DEFAULT 'CURRENT_TIMESTAMP',
	`updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
	`deleted_at` DATETIME NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8mb4_unicode_ci'
ENGINE=InnoDB
AUTO_INCREMENT=3
;


```

