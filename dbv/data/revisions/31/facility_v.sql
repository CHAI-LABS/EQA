CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `homestead`@`%` 
    SQL SECURITY DEFINER
VIEW `facility_v` AS
    SELECT 
		`f`.`id` AS `facility_id`,
        `f`.`facility_code` AS `facility_code`,
        `f`.`facility_name` AS `facility_name`,
        `f`.`cd4` AS `cd4`,
        `c`.`county_name` AS `county_name`,
        `sc`.`sub_county_name` AS `sub_county_name`
    FROM
        ((`facility` `f`
        LEFT JOIN `sub_county` `sc` ON ((`sc`.`id` = `f`.`sub_county_id`)))
        LEFT JOIN `county` `c` ON ((`c`.`id` = `sc`.`county_id`)))
    ORDER BY `f`.`facility_name`;
