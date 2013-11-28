ALTER TABLE `task`
ADD `cancelled` tinyint NOT NULL DEFAULT 0 COMMENT 'uloha zrusena v prubehu hry' AFTER `points`;

ALTER TABLE `task`
ADD INDEX `cancelled` (`cancelled`);
