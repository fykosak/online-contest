-- It's better to have same IDs for each year
-- WARNING: Overwrites data from previous years.
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELETE FROM `group` WHERE `id_group` IN (1,2,3,4);
INSERT INTO `group` (`id_group`, `id_year`, `to_show`, `type`, `code_name`, `text`, `allow_zeroes`, `inserted`, `updated`) VALUES
(1,	1,	'2015-12-02 17:00:00',	'serie',	'FoL',	'Normální Fyziklání.',	0,	'2015-12-02 00:26:06',	'2015-12-02 00:26:06'),
(2,	1,	'2015-12-02 18:00:00',	'serie',	'M',	'Hurry-up mechanika.',	1,	'2015-12-02 00:26:46',	'2015-12-02 00:26:46'),
(3,	1,	'2015-12-02 18:00:00',	'serie',	'E',	'Hurry-up elektřina.',	1,	'2015-12-02 00:27:44',	'2015-12-02 00:27:44'),
(4,	1,	'2015-12-02 18:00:00',	'serie',	'X',	'Hurry-up ostatní',	1,	'2015-12-02 00:27:44',	'2015-12-02 00:27:44');

DELETE FROM `period` WHERE `id_period` IN (1,2,3,4,5);
INSERT INTO `period` (`id_period`, `id_group`, `begin`, `end`, `allow_skip`, `time_penalty`, `reserve_size`) VALUES
(1,	1,	'2015-12-02 17:00:00',	'2015-12-02 18:29:59',	0,	60,	7),
(2,	1,	'2015-12-02 18:30:00',	'2015-12-02 19:59:59',	1,	60,	7),
(3,	2,	'2015-12-02 18:00:00',	'2015-12-02 18:29:59',	0,	60,	1),
(4,	3,	'2015-12-02 18:00:00',	'2015-12-02 18:29:59',	0,	60,	1),
(5,	4,	'2015-12-02 18:00:00',	'2015-12-02 18:29:59',	0,	60,	1);

DELETE FROM `year` WHERE `id_year` = 1;
INSERT INTO `year` (`id_year`, `name`, `registration_start`, `registration_end`, `game_start`, `game_end`, `inserted`, `updated`) VALUES
(1,	'5. ročník',	'2015-10-14 22:00:00',	'2015-12-01 11:59:59',	'2015-12-02 17:00:00',	'2015-12-02 19:59:59',	'2015-10-07 23:43:00',	'2015-10-07 23:44:38');

