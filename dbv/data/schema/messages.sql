CREATE TABLE `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `from` varchar(255) NOT NULL DEFAULT 'No Name',
  `email` varchar(255) DEFAULT 'No email',
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `status` int(11) DEFAULT '0',
  `date_sent` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1