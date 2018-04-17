CREATE TABLE `pt_equipment_results` (
  `equip_result_id` int(11) NOT NULL,
  `sample_id` int(11) NOT NULL,
  `cd3_absolute` float DEFAULT '0',
  `cd3_percent` float DEFAULT '0',
  `cd4_absolute` float DEFAULT '0',
  `cd4_percent` float DEFAULT '0',
  `other_absolute` float DEFAULT '0',
  `other_percent` float DEFAULT '0',
  `last_modified` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1