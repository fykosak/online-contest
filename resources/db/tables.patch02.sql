ALTER TABLE `task`
ADD `real_sig_digits` tinyint NULL COMMENT 'ocekavany pocet platnych cifer vysledku' AFTER `real_tolerance`,
ADD `answer_unit` varchar(255) NULL COMMENT 'jednotka vysledku, HTML fragment' AFTER `answer_real`,
CHANGE `name` `name_cs` varchar(250) COLLATE 'utf8_czech_ci' NOT NULL COMMENT 'nazev ukolu' AFTER `number`,
CHANGE `filename` `filename_cs` varchar(250) COLLATE 'utf8_bin' NOT NULL COMMENT 'neuhodnutelny nazev souboru se zadanim ukolu' AFTER `name_cs`,
ADD `name_en` varchar(250) COLLATE 'utf8_general_ci' NOT NULL COMMENT 'nazev ukolu' AFTER `name_cs`,
ADD `filename_en` varchar(250) COLLATE 'utf8_bin' NOT NULL COMMENT 'neuhodnutelny nazev souboru se zadanim ukolu' AFTER `filename_cs`;




ALTER TABLE `task_state`
ADD `inserted` DATETIME NOT NULL COMMENT 'cas vlozeni zaznamu' AFTER `substitute`;
