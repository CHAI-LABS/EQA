CREATE DEFINER=`root`@`localhost` TRIGGER `capa_response_BEFORE_INSERT` BEFORE INSERT ON `capa_response` FOR EACH ROW BEGIN
	SET new.uuid := (SELECT UUID());
END