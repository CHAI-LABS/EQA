CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_pt_labs_results`(
	IN `p_equipment_id` INT,
	IN `p_sample_id` INT,
	IN `p_lab_id` INT,
	IN `p_round_id` INT,
	IN `p_result` VARCHAR(50)

)
    SQL SECURITY INVOKER
BEGIN		
	IF EXISTS(SELECT id FROM pt_labs_results WHERE pt_round_id = p_round_id AND equipment_id = p_equipment_id AND pt_lab_id = p_lab_id AND pt_sample_id = p_sample_id) THEN
		UPDATE pt_labs_results SET result = p_result WHERE pt_round_id = p_round_id AND equipment_id = p_equipment_id AND pt_lab_id = p_lab_id AND pt_sample_id = p_sample_id;
	ELSE
		INSERT INTO pt_labs_results (`pt_round_id`,`equipment_id`,`pt_lab_id`,`pt_sample_id`,`result`) VALUES(p_round_id, p_equipment_id, p_lab_id, p_sample_id, p_result);
	END IF;
END