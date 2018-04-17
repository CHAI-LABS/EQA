CREATE DEFINER=`root`@`localhost` PROCEDURE `get_facility_readiness_data`(
	IN `pt_round_uuid` VARCHAR(36),
	IN `search_value` TEXT,
	IN `v_limit` INT,
	IN `v_offset` INT

)
BEGIN
	SET @pt_round_uuid = pt_round_uuid;
	SET @search_value = search_value;
	
	IF(v_limit IS NOT NULL AND v_offset IS NOT NULL) THEN
		SET @limit_stmt = CONCAT("LIMIT ", v_limit, ' OFFSET ', v_offset);
	ELSE
		SET @limit_stmt = "";
	END IF;
	
	SET @query = CONCAT('SELECT
		f.id as facility_id,
		f.facility_code, 
		f.facility_name,
		f.email,
		(CASE pr.`status` WHEN 0 THEN "In Review"
		WHEN 1 THEN "Complete"
		ELSE "No Response"
		END) as `status`,
		(IF(pr.verdict IS NULL,(IF(prr.responses = "N/A", "Okay", IF(prr.responses > 0, "Not Okay", "No Response"))), pr.verdict)) as `smart_status`
	FROM facility f
	LEFT JOIN participants p ON p.participant_facility = f.id
	LEFT JOIN participant_readiness pr ON pr.participant_id = p.uuid AND pr.pt_round_no = "', @pt_round_uuid,'"
	LEFT JOIN 
	(
		SELECT a.readiness_id, IFNULL(b.responses, "N/A") as responses FROM participant_readiness_responses a
		LEFT JOIN (SELECT readiness_id, count(response) as responses FROM participant_readiness_responses WHERE response = 0 AND response IS NOT NULL GROUP BY readiness_id) b ON b.readiness_id = a.readiness_id
		GROUP BY a.readiness_id
	) prr ON prr.readiness_id = pr.readiness_id
	WHERE f.facility_code IN (SELECT facility_code FROM facility_equipment_mapping)
	AND (
		f.facility_code LIKE "', "%", @search_value , "%", '"
		OR f.facility_name LIKE "', "%", @search_value , "%", '"
	)
	AND f.facility_code != 0
	AND f.id IN (SELECT participant_facility FROM participants)
	GROUP BY f.facility_code ', @limit_stmt);

	PREPARE stmt FROM @query;
	EXECUTE stmt;
	DEALLOCATE PREPARE stmt;
END