CREATE TABLE `capa_response` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `uuid` VARCHAR(255) NOT NULL,
  `round_uuid` VARCHAR(255) NOT NULL,
  `participant_uuid` VARCHAR(255) NOT NULL,
  `facility_id` INT NOT NULL,
  `occurrence` TEXT NULL,
  `cause` TEXT NULL,
  `correction` TEXT NULL,
  `effective` INT NULL DEFAULT 0,
  `prevention` TEXT NULL,
  `date_of_submission` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `approved` INT NULL DEFAULT 0,
  PRIMARY KEY (`id`));
