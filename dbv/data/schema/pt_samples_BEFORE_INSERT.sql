CREATE DEFINER=`root`@`localhost` TRIGGER `pt_samples_BEFORE_INSERT` BEFORE INSERT ON `pt_samples` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END