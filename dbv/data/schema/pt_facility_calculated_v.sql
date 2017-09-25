CREATE 
    ALGORITHM = UNDEFINED 
    SQL SECURITY DEFINER
VIEW `pt_facility_calculated_v` AS
    SELECT 
        `pds`.`id` AS `id`,
        `pds`.`round_id` AS `round_id`,
        `per`.`sample_id` AS `sample_id`,
        `pds`.`equipment_id` AS `equipment_id`,
        `prv`.`facility_id` AS `facility_id`,
        ROUND(AVG(`per`.`cd3_absolute`), 0) AS `cd3_absolute_mean`,
        ROUND(STD(`per`.`cd3_absolute`), 2) AS `cd3_absolute_sd`,
        (2 * ROUND(STD(`per`.`cd3_absolute`), 2)) AS `double_cd3_absolute_sd`,
        (ROUND(AVG(`per`.`cd3_absolute`), 0) + (2 * ROUND(STD(`per`.`cd3_absolute`), 2))) AS `cd3_absolute_upper_limit`,
        (ROUND(AVG(`per`.`cd3_absolute`), 2) - (2 * ROUND(STD(`per`.`cd3_absolute`), 2))) AS `cd3_absolute_lower_limit`,
        CEILING(((STD(`per`.`cd3_absolute`) / AVG(`per`.`cd3_absolute`)) * 100)) AS `cd3_absolute_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`cd3_absolute`) / AVG(`per`.`cd3_absolute`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `cd3_absolute_outcome`,
        ROUND(AVG(`per`.`cd3_percent`), 0) AS `cd3_percent_mean`,
        ROUND(STD(`per`.`cd3_percent`), 2) AS `cd3_percent_sd`,
        (2 * ROUND(STD(`per`.`cd3_percent`), 2)) AS `double_cd3_percent_sd`,
        (ROUND(AVG(`per`.`cd3_percent`), 0) + (2 * ROUND(STD(`per`.`cd3_percent`), 2))) AS `cd3_percent_upper_limit`,
        (ROUND(AVG(`per`.`cd3_percent`), 2) - (2 * ROUND(STD(`per`.`cd3_percent`), 2))) AS `cd3_percent_lower_limit`,
        CEILING(((STD(`per`.`cd3_percent`) / AVG(`per`.`cd3_percent`)) * 100)) AS `cd3_percent_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`cd3_percent`) / AVG(`per`.`cd3_percent`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `cd3_percent_outcome`,
        ROUND(AVG(`per`.`cd4_absolute`), 0) AS `cd4_absolute_mean`,
        ROUND(STD(`per`.`cd4_absolute`), 2) AS `cd4_absolute_sd`,
        (2 * ROUND(STD(`per`.`cd4_absolute`), 2)) AS `double_cd4_absolute_sd`,
        (ROUND(AVG(`per`.`cd4_absolute`), 0) + (2 * ROUND(STD(`per`.`cd4_absolute`), 2))) AS `cd4_absolute_upper_limit`,
        (ROUND(AVG(`per`.`cd4_absolute`), 2) - (2 * ROUND(STD(`per`.`cd4_absolute`), 2))) AS `cd4_absolute_lower_limit`,
        CEILING(((STD(`per`.`cd4_absolute`) / AVG(`per`.`cd4_absolute`)) * 100)) AS `cd4_absolute_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`cd4_absolute`) / AVG(`per`.`cd4_absolute`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `cd4_absolute_outcome`,
        ROUND(AVG(`per`.`cd4_percent`), 0) AS `cd4_percent_mean`,
        ROUND(STD(`per`.`cd4_percent`), 2) AS `cd4_percent_sd`,
        (2 * ROUND(STD(`per`.`cd4_percent`), 2)) AS `double_cd4_percent_sd`,
        (ROUND(AVG(`per`.`cd4_percent`), 0) + (2 * ROUND(STD(`per`.`cd4_percent`), 2))) AS `cd4_percent_upper_limit`,
        (ROUND(AVG(`per`.`cd4_percent`), 2) - (2 * ROUND(STD(`per`.`cd4_percent`), 2))) AS `cd4_percent_lower_limit`,
        CEILING(((STD(`per`.`cd4_percent`) / AVG(`per`.`cd4_percent`)) * 100)) AS `cd4_percent_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`cd4_percent`) / AVG(`per`.`cd4_percent`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `cd4_percent_outcome`,
        ROUND(AVG(`per`.`other_absolute`), 0) AS `other_absolute_mean`,
        ROUND(STD(`per`.`other_absolute`), 2) AS `other_absolute_sd`,
        (2 * ROUND(STD(`per`.`other_absolute`), 2)) AS `double_other_absolute_sd`,
        (ROUND(AVG(`per`.`other_absolute`), 0) + (2 * ROUND(STD(`per`.`other_absolute`), 2))) AS `other_absolute_upper_limit`,
        (ROUND(AVG(`per`.`other_absolute`), 2) - (2 * ROUND(STD(`per`.`other_absolute`), 2))) AS `other_absolute_lower_limit`,
        CEILING(((STD(`per`.`other_absolute`) / AVG(`per`.`other_absolute`)) * 100)) AS `other_absolute_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`other_absolute`) / AVG(`per`.`other_absolute`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `other_absolute_outcome`,
        ROUND(AVG(`per`.`other_percent`), 0) AS `other_percent_mean`,
        ROUND(STD(`per`.`other_percent`), 2) AS `other_percent_sd`,
        (2 * ROUND(STD(`per`.`other_percent`), 2)) AS `double_other_percent_sd`,
        (ROUND(AVG(`per`.`other_percent`), 0) + (2 * ROUND(STD(`per`.`other_percent`), 2))) AS `other_percent_upper_limit`,
        (ROUND(AVG(`per`.`other_percent`), 2) - (2 * ROUND(STD(`per`.`other_percent`), 2))) AS `other_percent_lower_limit`,
        CEILING(((STD(`per`.`other_percent`) / AVG(`per`.`other_percent`)) * 100)) AS `other_percent_cv`,
        (CASE
            WHEN (CEILING(((STD(`per`.`other_percent`) / AVG(`per`.`other_percent`)) * 100)) > 28) THEN 'Failed'
            ELSE 'Passed'
        END) AS `other_percent_outcome`,
        `pds`.`doc_path` AS `doc_path`
    FROM
        ((`pt_data_submission` `pds`
        JOIN `pt_equipment_results` `per` ON ((`pds`.`id` = `per`.`equip_result_id`)))
        JOIN `participant_readiness_v` `prv` ON ((`prv`.`p_id` = `pds`.`participant_id`)))
    GROUP BY `per`.`sample_id` , `pds`.`equipment_id` , `prv`.`facility_id`