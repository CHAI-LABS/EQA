CREATE DEFINER=`root`@`localhost` TRIGGER `questionnairs_BEFORE_INSERT` BEFORE INSERT ON `questionnairs` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END