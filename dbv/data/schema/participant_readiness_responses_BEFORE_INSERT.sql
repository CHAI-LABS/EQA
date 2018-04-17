CREATE DEFINER=`root`@`localhost` TRIGGER `eqa`.`participant_readiness_responses_BEFORE_INSERT` BEFORE INSERT ON `participant_readiness_responses` FOR EACH ROW
BEGIN
SET new.uuid := (SELECT UUID());
END