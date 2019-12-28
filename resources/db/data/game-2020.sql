-- It's better to have same IDs for each year
-- WARNING: Overwrites data from previous years.
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELETE FROM `group`;
INSERT INTO `group` (`id_group`, `id_year`, `to_show`, `type`, `code_name`, `text`, `allow_zeroes`, `inserted`, `updated`) VALUES
(1,	1,	'2020-11-25 17:00:00',	'serie',	'FoL',	'Normální Fyziklání.',	0,	'2020-11-25 00:26:06',	'2020-11-25 00:26:06'),
(2,	1,	'2020-11-25 18:00:00',	'serie',	'M',	'Hurry-up mechanika.',	1,	'2020-11-25 00:26:46',	'2020-11-25 00:26:46'),
(3,	1,	'2020-11-25 18:00:00',	'serie',	'E',	'Hurry-up elektřina.',	1,	'2020-11-25 00:27:44',	'2020-11-25 00:27:44'),
(4,	1,	'2020-11-25 18:00:00',	'serie',	'X',	'Hurry-up ostatní',	1,	'2020-11-25 00:27:44',	'2020-11-25 00:27:44');

DELETE FROM `period`;
INSERT INTO `period` (`id_period`, `id_group`, `begin`, `end`, `allow_skip`, `has_bonus`, `time_penalty`, `reserve_size`) VALUES
(1,	1,	'2020-11-25 17:00:00',	'2020-11-25 18:29:59',	0,	0,	60,	7),
(2,	1,	'2020-11-25 18:30:00',	'2020-11-25 19:59:59',	1,	0,	60,	7),
(3,	2,	'2020-11-25 18:00:00',	'2020-11-25 18:29:59',	0,	1,	60,	1),
(4,	3,	'2020-11-25 18:00:00',	'2020-11-25 18:29:59',	0,	1,	60,	1),
(5,	4,	'2020-11-25 18:00:00',	'2020-11-25 18:29:59',	0,	1,	60,	1),
(6,	2,	'2020-11-25 18:30:00',	'2020-11-25 19:59:59',	1,	0,	60,	1),
(7,	3,	'2020-11-25 18:30:00',	'2020-11-25 19:59:59',	1,	0,	60,	1),
(8,	4,	'2020-11-25 18:30:00',	'2020-11-25 19:59:59',	1,	0,	60,	1);

DELETE FROM `year` WHERE `id_year` = 1;
INSERT INTO `year` (`id_year`, `name`, `registration_start`, `registration_end`, `game_start`, `game_end`, `inserted`, `updated`) VALUES
(1,	'10. ročník',	'2020-10-01 00:00:00',	'2020-11-23 23:59:59',	'2020-11-25 17:00:00',	'2020-11-25 19:59:59',	'2017-10-07 23:43:00',	'2017-10-07 23:44:38');

