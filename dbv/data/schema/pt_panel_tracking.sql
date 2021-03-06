CREATE TABLE `pt_panel_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) NOT NULL,
  `pt_batch_id` int(11) NOT NULL,
  `pt_readiness_id` int(11) NOT NULL,
  `panel_preparation_date` date NOT NULL,
  `panel_preparation_notes` text,
  `courier_collection_date` date DEFAULT NULL,
  `courier_company` text,
  `courier_official` text,
  `courier_dispatch_notes` text,
  `participant_received_date` date DEFAULT NULL,
  `panel_condition_comment` text,
  `panel_received_entered` datetime DEFAULT NULL,
  `receipt` int(11) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `panel_condition` int(11) DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid_UNIQUE` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1