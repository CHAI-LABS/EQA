CREATE 
     OR REPLACE ALGORITHM = UNDEFINED 
    DEFINER = `homestead`@`%` 
    SQL SECURITY DEFINER
VIEW `participant_readiness_v` AS
    SELECT 
        `p`.`id` AS `p_id`,
        `p`.`uuid` AS `uuid`,
        `p`.`participant_fname` AS `firstname`,
        `p`.`participant_lname` AS `lastname`,
        `p`.`participant_id` AS `username`,
        `p`.`participant_email` AS `email_address`,
        `p`.`participant_password` AS `password`,
        `p`.`participant_phonenumber` AS `phone`,
        `p`.`participant_sex` AS `sex`,
        `p`.`participant_age` AS `age`,
        `p`.`participant_education` AS `education`,
        `p`.`participant_experience` AS `experience`,
        `p`.`user_type` AS `user_type`,
        `p`.`status` AS `status`,
        `p`.`participant_facility` AS `facility_id`,
        `f`.`facility_code` AS `facility_code`,
        `f`.`facility_name` AS `facility_name`,
        `f`.`email` AS `facility_email`,
        `f`.`telephone` AS `telephone`,
        `f`.`alt_telephone` AS `alt_telephone`,
        `p`.`approved` AS `approved`
    FROM
        (`participants` `p`
        JOIN `facility` `f` ON ((`f`.`id` = `p`.`participant_facility`)));
