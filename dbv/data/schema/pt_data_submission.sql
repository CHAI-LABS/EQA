CREATE TABLE `pt_data_submission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `round_id` int(11) DEFAULT NULL,
  `participant_id` int(11) DEFAULT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT '0' COMMENT 'This shows that the participant''s data has been sent to NHRL. 1 = Sent, 0 = Not Sent',
  `verdict` int(11) DEFAULT '2' COMMENT 'This shows that the QA has either accepted, rejected or still to judge. 1 = Accepted, 0 = Rejected, \n2 = Awaiting Verdict',
  `date_entered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `doc_path` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1