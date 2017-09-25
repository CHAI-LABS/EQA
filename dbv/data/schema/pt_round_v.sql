USE `eqa`;
CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    SQL SECURITY DEFINER
VIEW `pt_round_v` AS
    SELECT 
        `pt_round`.`id` AS `id`,
        `pt_round`.`uuid` AS `uuid`,
        `pt_round`.`pt_round_no` AS `pt_round_no`,
        `pt_round`.`from` AS `from`,
        `pt_round`.`to` AS `to`,
        `pt_round`.`date_of_entry` AS `date_of_entry`,
        `pt_round`.`tag` AS `tag`,
        `pt_round`.`blood_lab_unit_id` AS `lab_unit`,
        (CASE `pt_round`.`pt_status`
            WHEN 1 THEN 'active'
            ELSE 'inactive'
        END) AS `status`,
        (CASE
            WHEN (CURDATE() > `pt_round`.`to`) THEN 'previous'
            WHEN (CURDATE() BETWEEN `pt_round`.`from` AND `pt_round`.`to`) THEN 'ongoing'
            WHEN (CURDATE() < `pt_round`.`from`) THEN 'future'
            ELSE 'previous'
        END) AS `type`
    FROM
        `pt_round`;
