-- Adminer 2.3.2 dump
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DELETE FROM `group` WHERE `id_group` IN (5,6,7,8);
INSERT INTO `group` (`id_group`, `id_year`, `to_show`, `type`, `code_name`, `text`, `allow_zeroes`, `inserted`, `updated`) VALUES
(5,	1,	'2012-12-06 17:00:00',	'serie',	'FoL',	'Normální Fyziklání.',	0,	'2012-05-03 00:26:06',	'2012-05-03 00:26:06'),
(6,	1,	'2012-12-06 18:00:00',	'serie',	'M',	'Hurry-up mechanika.',	1,	'2012-05-03 00:26:46',	'2012-05-03 00:26:46'),
(7,	1,	'2012-12-06 18:00:00',	'serie',	'E',	'Hurry-up elektřina.',	1,	'2012-05-03 00:27:44',	'2012-05-03 00:27:44'),
(8,	1,	'2012-12-06 18:00:00',	'serie',	'X',	'Hurry-up ostatní',	1,	'2012-05-03 00:27:44',	'2012-05-03 00:27:44');

DELETE FROM `period` WHERE `id_period` IN (6,7,8,9,10);
INSERT INTO `period` (`id_period`, `id_group`, `begin`, `end`, `allow_skip`, `time_penalty`, `reserve_size`) VALUES
(6,	5,	'2012-12-06 17:00:00',	'2012-12-06 18:29:59',	0,	60,	5),
(7,	5,	'2012-12-06 18:30:00',	'2012-12-06 19:59:59',	1,	60,	5),
(8,	6,	'2012-12-06 18:00:00',	'2012-12-06 18:29:59',	0,	60,	1),
(9,	7,	'2012-12-06 18:00:00',	'2012-12-06 18:29:59',	0,	60,	1),
(10,	8,	'2012-12-06 18:00:00',	'2012-12-06 18:29:59',	0,	60,	1);

DELETE FROM `year` WHERE `id_year` = 2;
INSERT INTO `year` (`id_year`, `name`, `registration_start`, `registration_end`, `game_start`, `game_end`, `inserted`, `updated`) VALUES
(2,	'2. ročník',	'2012-10-10 00:00:00',	'2012-12-05 22:59:59',	'2012-12-06 17:00:00',	'2012-12-06 19:59:59',	'2012-10-07 23:43:00',	'2012-10-07 23:44:38');

