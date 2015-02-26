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

SELECT * FROM training_entry;