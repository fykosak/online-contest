-- Adminer 4.2.5 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `notification_id` int(25) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE utf8_czech_ci NOT NULL COMMENT 'text notifikace',
  `lang` enum('cs','en') COLLATE utf8_czech_ci NOT NULL COMMENT 'jazyk notifikace',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka vlozena do systemu',
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='notifikace na nastenku';

INSERT INTO `notification` (`notification_id`, `message`, `lang`, `created`) VALUES
(1,	'Soutěž začala.',                               'cs',	'2016-11-30 17:00:00'),
(2,	'The game has started.',                        'en',	'2016-11-30 17:00:00'),
(3,	'Hurry up začne za 10 minut.',                  'cs',	'2016-11-30 17:50:00'),
(4,	'Hurry up begins in 10 minutes.',               'en',	'2016-11-30 17:50:00'),
(5,	'Hurry up začne za 5 minut.',                   'cs',	'2016-11-30 17:55:00'),
(6,	'Hurry up begins in 5 minutes.',                'en',	'2016-11-30 17:55:00'),
(7,	'Hurry up začal.',                              'cs',	'2016-11-30 18:00:00'),
(8,	'Hurry up has begun.',                          'en',	'2016-11-30 18:00:00'),
(9,	'Posledních 5 minut Hurry upu.',                'cs',	'2016-11-30 18:25:00'),
(10,	'Last 5 minutes of Hurry up.',                  'en',	'2016-11-30 18:25:00'),
(11,	'Hurry up skončil.',                            'cs',	'2016-11-30 18:30:00'),
(12,	'Hurry up has ended.',                          'en',	'2016-11-30 18:30:00'),
(13,	'Můžete začít přeskakovat úlohy.',              'cs',	'2016-11-30 18:30:00'),
(14,	'You can start skipping problems.',             'en',	'2016-11-30 18:30:00'),
(15,	'Poslední půl hodiny soutěže.',                 'cs',	'2016-11-30 19:30:00'),
(16,	'Last half hour of the competition.',           'en',	'2016-11-30 19:30:00'),
(17,	'Výsledkovka byla právě zmražena.',             'cs',	'2016-11-30 19:40:00'),
(18,	'Stats have been freezed.',                     'en',	'2016-11-30 19:40:00'),
(19,	'Posledních 10 minut soutěže.',                 'cs',	'2016-11-30 19:50:00'),
(20,	'Last 10 minutes of the competition.',          'en',	'2016-11-30 19:50:00'),
(21,	'Posledních 5 minut soutěže.',                  'cs',	'2016-11-30 19:55:00'),
(22,	'Last 5 minutes of the competition.',           'en',	'2016-11-30 19:55:00'),
(23,	'Vyplňte nám prosím anketu.',                   'cs',	'2016-11-30 19:55:00'),
(24,	'Please fill in our poll.',                     'en',	'2016-11-30 19:55:00'),
(25,	'Soutěž právě skončila, děkujeme za účast.',    'cs',	'2016-11-30 20:00:00'),
(26,	'The competition has ended, thank you for participating.',	'en',	'2016-11-30 20:00:00');

-- 2016-11-30 13:52:28
