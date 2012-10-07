ALTER TABLE `competitor`
ADD `email` varchar(150) COLLATE 'utf8_czech_ci' NULL AFTER `name`,
ADD `study_year` tinyint NULL COMMENT 'rocnik SS (1..4)/(6..9)' AFTER `email`,
COMMENT='informace o soutezicich';
