CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `equipment_facilities_v` AS select `e`.`id` AS `e_id`,`e`.`equipment_name` AS `e_name`,`e`.`uuid` AS `e_uuid`,`f`.`facility_code` AS `facility_code`,`f`.`facility_name` AS `facility_name`,`c`.`county_name` AS `county_name`,`sc`.`sub_county_name` AS `sub_county_name` from ((((`facility` `f` left join `sub_county` `sc` on((`sc`.`id` = `f`.`sub_county_id`))) left join `county` `c` on((`c`.`id` = `sc`.`county_id`))) join `facility_equipment_mapping` `fem` on((`fem`.`facility_code` = `f`.`facility_code`))) join `equipment` `e` on((`fem`.`equipment_id` = `e`.`id`))) order by `e`.`id`