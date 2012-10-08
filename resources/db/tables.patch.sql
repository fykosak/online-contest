ALTER TABLE `competitor`
ADD `email` varchar(150) COLLATE 'utf8_czech_ci' NULL AFTER `name`,
ADD `study_year` tinyint NULL COMMENT 'rocnik SS (1..4)/(6..9)' AFTER `email`,
COMMENT='informace o soutezicich';

ALTER TABLE `team`
CHANGE `category` `category` enum('high_school','open','abroad','hs_a','hs_b','hs_c') COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'soutezni kategorie' AFTER `password`,
COMMENT='Soutezni tymy';