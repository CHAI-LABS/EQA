CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_pt_testers_result`(IN `p_equipment_id` INT,
	IN `p_sample_id` INT,
	IN `p_tester_id` INT,
	IN `p_round_id` INT,
	IN `p_result` VARCHAR(50))
BEGIN
IF EXISTS(SELECT id FROM pt_testers_result WHERE pt_round_id = p_round_id AND equipment_id = p_equipment_id AND pt_tester_id = p_tester_id AND pt_sample_id = p_sample_id) THEN
		UPDATE pt_testers_result SET result = p_result WHERE pt_round_id = p_round_id AND equipment_id = p_equipment_id AND pt_tester_id = p_tester_id AND pt_sample_id = p_sample_id;
	ELSE
		INSERT INTO pt_testers_result (`pt_round_id`,`equipment_id`,`pt_tester_id`,`pt_sample_id`,`result`) VALUES(p_round_id, p_equipment_id, p_tester_id, p_sample_id, p_result);
	END IF;
END