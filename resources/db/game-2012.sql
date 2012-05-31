-- Adminer 2.3.2 dump
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `group` (`id_group`, `id_year`, `to_show`, `type`, `code_name`, `text`, `allow_zeroes`, `inserted`, `updated`) VALUES
(1,	1,	'2012-05-31 17:00:00',	'serie',	'FoL',	'Normální Fyziklání.',	0,	'2012-05-03 00:26:06',	'2012-05-03 00:26:06'),
(2,	1,	'2012-05-31 18:00:00',	'serie',	'M',	'Hurry-up mechanika.',	1,	'2012-05-03 00:26:46',	'2012-05-03 00:26:46'),
(3,	1,	'2012-05-31 18:00:00',	'serie',	'E',	'Hurry-up elektřina.',	1,	'2012-05-03 00:27:44',	'2012-05-03 00:27:44'),
(4,	1,	'2012-05-31 18:00:00',	'serie',	'X',	'Hurry-up ostatní',	1,	'2012-05-03 00:27:44',	'2012-05-03 00:27:44');

INSERT INTO `period` (`id_period`, `id_group`, `begin`, `end`, `allow_skip`, `time_penalty`, `reserve_size`) VALUES
(1,	1,	'2012-05-31 17:00:00',	'2012-05-31 18:29:59',	0,	60,	5),
(2,	1,	'2012-05-31 18:30:00',	'2012-05-31 19:59:59',	1,	60,	5),
(3,	2,	'2012-05-31 18:00:00',	'2012-05-31 18:29:59',	0,	60,	1),
(4,	3,	'2012-05-31 18:00:00',	'2012-05-31 18:29:59',	0,	60,	1),
(5,	4,	'2012-05-31 18:00:00',	'2012-05-31 18:29:59',	0,	60,	1);

INSERT INTO `year` (`id_year`, `name`, `registration_start`, `registration_end`, `game_start`, `game_end`, `inserted`, `updated`) VALUES
(1,	'1. ročník',	'2012-05-04 08:00:00',	'2012-05-30 23:59:59',	'2012-05-31 17:00:00',	'2012-05-31 19:59:59',	'2012-05-03 00:25:10',	'2012-05-03 00:25:19');
