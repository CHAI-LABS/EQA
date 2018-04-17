CREATE DEFINER=`root`@`localhost` TRIGGER `pt_data_log_BEFORE_INSERT` BEFORE INSERT ON `pt_data_log` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END