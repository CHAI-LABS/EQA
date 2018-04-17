CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `users_v` AS select `participants`.`uuid` AS `uuid`,`participants`.`participant_fname` AS `firstname`,`participants`.`participant_lname` AS `lastname`,`participants`.`participant_id` AS `username`,`participants`.`participant_email` AS `email_address`,`participants`.`participant_phonenumber` AS `phone`,`participants`.`user_type` AS `user_type`,`participants`.`avatar` AS `avatar`,`participants`.`approved` AS `approved`,`participants`.`status` AS `status`,`participants`.`participant_password` AS `password` from `participants` union select `user`.`uuid` AS `uuid`,`user`.`user_firstname` AS `firstname`,`user`.`user_lastname` AS `lastname`,`user`.`username` AS `username`,`user`.`email_address` AS `email_address`,`user`.`phonenumber` AS `phone`,`user`.`user_type` AS `user_type`,`user`.`avatar` AS `avatar`,`user`.`approved` AS `approved`,`user`.`status` AS `status`,`user`.`password` AS `password` from `user`