CREATE TABLE `participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `participant_id` varchar(255) NOT NULL,
  `uuid` varchar(255) DEFAULT NULL,
  `participant_fname` varchar(255) NOT NULL,
  `participant_lname` varchar(255) DEFAULT NULL,
  `participant_phonenumber` varchar(20) NOT NULL,
  `participant_facility` int(11) NOT NULL,
  `participant_email` varchar(255) NOT NULL,
  `user_type` varchar(45) DEFAULT NULL,
  `participant_password` varchar(255) NOT NULL,
  `avatar` text,
  `approved` int(11) NOT NULL DEFAULT '0',
  `status` int(11) NOT NULL DEFAULT '0',
  `date_registered` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `confirm_token` longtext,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1