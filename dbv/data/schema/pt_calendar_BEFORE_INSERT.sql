CREATE DEFINER=`root`@`localhost` TRIGGER `pt_calendar_BEFORE_INSERT` BEFORE INSERT ON `pt_calendar` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END