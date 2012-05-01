-- Adminer 2.3.2 dump
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

INSERT INTO `answer` (`id_answer`, `id_team`, `id_task`, `answer_str`, `answer_int`, `answer_real`, `inserted`, `updated`) VALUES
(3,	2,	1,	NULL,	10,	NULL,	'2012-05-01 02:26:46',	'2012-05-01 02:26:46'),
(4,	2,	1,	NULL,	15,	NULL,	'2012-05-01 02:26:53',	'2012-05-01 02:26:53');

INSERT INTO `group` (`id_group`, `id_year`, `to_show`, `type`, `code_name`, `text`, `allow_zeroes`, `inserted`, `updated`) VALUES
(1,	2,	'2012-05-01 02:21:23',	'serie',	'KLANI',	'bla bla fyziklání',	0,	'2012-05-01 02:21:23',	'2012-05-01 02:21:23'),
(2,	2,	'2012-05-01 02:21:51',	'serie',	'HUI',	'',	0,	'2012-05-01 02:21:51',	'2012-05-01 02:21:51'),
(3,	2,	'2012-05-01 02:22:01',	'serie',	'HUII',	'',	0,	'2012-05-01 02:22:01',	'2012-05-01 02:22:01'),
(4,	2,	'2012-05-01 02:22:14',	'serie',	'HUIII',	'bla bla',	0,	'2012-05-01 02:22:14',	'2012-05-01 02:22:14');

INSERT INTO `task` (`id_task`, `id_group`, `number`, `name`, `points`, `answer_type`, `answer_str`, `answer_int`, `answer_real`, `real_tolerance`, `inserted`, `updated`) VALUES
(1,	1,	1,	'první',	5,	'int',	NULL,	15,	NULL,	NULL,	'2012-05-01 02:23:26',	'2012-05-01 02:23:26'),
(2,	1,	2,	'druhá',	7,	'str',	'nic',	NULL,	NULL,	NULL,	'2012-05-01 02:23:59',	'2012-05-01 02:23:59');

INSERT INTO `year` (`id_year`, `name`, `registration_start`, `registration_end`, `game_start`, `game_end`, `inserted`, `updated`) VALUES
(2,	'Test I',	'2012-05-01 02:19:56',	'2012-12-31 23:59:59',	'2012-05-01 02:19:56',	'2012-12-31 23:59:59',	'2012-05-01 02:19:56',	'2012-05-01 02:19:56');
