CREATE TABLE `unable_response` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) DEFAULT NULL,
  `round_uuid` varchar(255) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `participant_uuid` varchar(255) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `reason` varchar(45) DEFAULT NULL,
  `detail` text,
  `date_sent` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `viewed` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1