CREATE ALGORITHM=UNDEFINED DEFINER=`homestead`@`%` SQL SECURITY DEFINER VIEW `data_entry_v` AS select `pds`.`round_id` AS `round_id`,`pr`.`uuid` AS `round_uuid`,`pds`.`participant_id` AS `participant_id`,`pds`.`equipment_id` AS `equipment_id`,`pds`.`lot_number` AS `lot_number`,`pds`.`reagent_name` AS `reagent_name`,`pds`.`expiry_date` AS `expiry_date`,`pds`.`status` AS `eq_status`,`pds`.`verdict` AS `verdict`,`pds`.`doc_path` AS `doc_path`,`per`.`sample_id` AS `sample_id`,`per`.`cd3_absolute` AS `cd3_absolute`,`per`.`cd3_percent` AS `cd3_percent`,`per`.`cd4_absolute` AS `cd4_absolute`,`per`.`cd4_percent` AS `cd4_percent`,`per`.`other_absolute` AS `other_absolute`,`per`.`other_percent` AS `other_percent`,`per`.`last_modified` AS `last_modified` from ((`pt_equipment_results` `per` left join `pt_data_submission` `pds` on((`pds`.`id` = `per`.`sample_id`))) left join `pt_round` `pr` on((`pr`.`id` = `pds`.`round_id`)))