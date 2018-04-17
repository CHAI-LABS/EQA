CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_pt_calendar`(
	IN `v_item_id` INT,
	IN `v_round_id` INT,
	IN `v_date_from` DATE,
	IN `v_date_to` DATE

)
    SQL SECURITY INVOKER
BEGIN
	IF EXISTS(SELECT id FROM pt_calendar WHERE calendar_item_id = v_item_id AND pt_round_id = v_round_id) THEN
		UPDATE pt_calendar SET date_from = v_date_from, date_to = v_date_to WHERE calendar_item_id = v_item_id AND pt_round_id = v_round_id;
	ELSE
		INSERT INTO pt_calendar(`pt_round_id`, `calendar_item_id`, `date_from`, `date_to`) VALUES (v_round_id, v_item_id, v_date_from, v_date_to);
	END IF;
END