CREATE TABLE IF NOT EXISTS `record_packages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `event_historical_records` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `package_id` int(11) NOT NULL,
  `distance` int(11) DEFAULT NULL,
  `stroke` varchar(50) DEFAULT NULL,
  `jenis_kelamin` varchar(10) DEFAULT NULL,
  `age_group` varchar(50) DEFAULT NULL,
  `holder_name` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `record_year` varchar(10) DEFAULT NULL,
  `record_time` varchar(20) DEFAULT NULL,
  `record_time_ms` int(11) DEFAULT NULL,
  `source_event_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
