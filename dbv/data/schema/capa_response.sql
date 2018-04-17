CREATE TABLE `capa_response` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `round_uuid` varchar(255) NOT NULL,
  `participant_uuid` varchar(255) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `occurrence` text,
  `cause` text,
  `correction` text,
  `effective` int(11) DEFAULT '0',
  `prevention` text,
  `date_of_submission` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` int(11) DEFAULT '0',
  `status` int(11) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1