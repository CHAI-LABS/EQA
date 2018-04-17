CREATE DEFINER=`root`@`localhost` TRIGGER `pt_panel_tracking_BEFORE_INSERT` BEFORE INSERT ON `pt_panel_tracking` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END