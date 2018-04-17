CREATE DEFINER=`root`@`localhost` TRIGGER `pt_batch_tube_BEFORE_INSERT` BEFORE INSERT ON `pt_batch_tube` FOR EACH ROW BEGIN
SET new.uuid := (SELECT UUID());
END