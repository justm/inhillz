ALTER TABLE training_entry add title VARCHAR(150) after duration;
ALTER TABLE training_entry add total_elapsed_time DECIMAL(9,3) after duration;
ALTER TABLE training_entry add total_timer_time DECIMAL(9,3) after duration; -- changes duration column later
ALTER TABLE training_entry add raw_file VARCHAR(150);
ALTER TABLE training_entry add data_file VARCHAR(150);	
ALTER TABLE training_entry add start_time INT after `date`; -- changes date column later

-- calculate missing values
UPDATE training_entry SET title = substring(description,1,35);
UPDATE training_entry SET start_time = UNIX_TIMESTAMP(STR_TO_DATE(`date`, '%Y-%m-%d')) + 10000;
UPDATE training_entry SET total_timer_time = (HOUR(duration) * 3600 + MINUTE(duration) * 60 + SECOND(duration));
UPDATE training_entry SET total_elapsed_time = total_timer_time;

-- modify columns to NOT NULL
ALTER TABLE training_entry modify title VARCHAR(150) NOT NULL;
ALTER TABLE training_entry modify total_elapsed_time DECIMAL(9,3) NOT NULL;
ALTER TABLE training_entry modify total_timer_time DECIMAL(9,3) NOT NULL;
ALTER TABLE training_entry modify start_time INT NOT NULL;

-- -----------------------------------------------------
-- Table `gear`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `gear` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
	`name` VARCHAR(50) NOT NULL,
	`weight` DECIMAL (4,2) NOT NULL DEFAULT 0.00,
	`cda_coef` DECIMAL(8,7) NOT NULL DEFAULT 0.5,
	`crr_coef` DECIMAL(8,7) NOT NULL DEFAULT 0.5,
	`id_user` INT NOT NULL,
    PRIMARY KEY (`id`),
	FOREIGN KEY (`id_user`) REFERENCES `user` (`id`) ON DELETE CASCADE
)  ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARACTER SET=utf8;

INSERT INTO `gear` VALUES
	(1, 'Fuji Altamira SL training', 7.7, 0.5, 0.0042,1),
	(2, 'Radon', 8.0, 0.38, 0.00343,1),
	(3, 'BMC', 7.7, 0.5, 0.0042,1);


ALTER TABLE training_entry add id_gear INT;
ALTER TABLE training_entry ADD FOREIGN KEY (id_gear) REFERENCES gear(id);

-- -----------------------------------------------------
-- Table `training_entry_record`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `training_entry_record` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
	`timestamp` INT,
	`position_lat` INT,
	`position_long` INT,
	`distance` DECIMAL(9,2),
	`altitude` DECIMAL(7,2),
	`speed` DECIMAL(6,3),
	`heart_rate` INT(3),
	`cadence` INT(3),
	`temperature` DECIMAL(4,1),
	`power` INT(4),
	`est_power` INT(4),
	`id_training_entry` INT NOT NULL,
	PRIMARY KEY (`id`),
	FOREIGN KEY (`id_training_entry`) REFERENCES `training_entry` (`id`) ON DELETE CASCADE
)  ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARACTER SET=utf8;


SELECT * FROM training_entry;