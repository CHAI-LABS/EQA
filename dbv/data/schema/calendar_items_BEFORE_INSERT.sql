CREATE DEFINER=`root`@`localhost` TRIGGER `calendar_items_BEFORE_INSERT` BEFORE INSERT ON `calendar_items` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END