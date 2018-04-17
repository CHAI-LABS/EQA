CREATE DEFINER=`root`@`localhost` TRIGGER `pt_testers_BEFORE_INSERT` BEFORE INSERT ON `pt_testers` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END