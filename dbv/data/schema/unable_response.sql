CREATE TABLE `eqa`.`unable_response` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `round_uuid` VARCHAR(255) NOT NULL,
  `equipment_id` INT NOT NULL,
  `participant_uuid` VARCHAR(255) NULL,
  `facility_id` INT NULL,
  `reason` VARCHAR(45) NULL,
  `detail` TEXT NULL,
  `date_sent` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `viewed` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
