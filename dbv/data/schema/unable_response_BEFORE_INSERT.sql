CREATE DEFINER=`root`@`localhost` TRIGGER `unable_response_BEFORE_INSERT` BEFORE INSERT ON `unable_response` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END