CREATE DEFINER=`root`@`localhost` PROCEDURE `proc_get_calendar_details`(
	IN `pt_round_id` INT
)
BEGIN
	SELECT ci.uuid as calendar_item_id, ci.item_name as calendar_item, ptc.date_from, ptc.date_to  FROM calendar_items ci
	LEFT JOIN pt_calendar ptc ON ptc.calendar_item_id = ci.id
	LEFT JOIN pt_round ptr ON ptr.id = ptc.pt_round_id AND ptr.id = pt_round_id;
END