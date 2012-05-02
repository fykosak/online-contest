-- Adminer 2.3.2 dump
SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer` (
  `id_answer` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_team` int(25) unsigned NOT NULL COMMENT 'tym, ktery hada kod',
  `id_task` int(25) unsigned NOT NULL COMMENT 'ukol, jehoz kod se hada',
  `answer_str` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'sloupec pro retezcovou odpoved',
  `answer_int` int(25) DEFAULT NULL COMMENT 'sloupec pro celociselnou odpoved',
  `answer_real` double DEFAULT NULL COMMENT 'sloupec pro realnou odpoved',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_answer`),
  UNIQUE KEY `id_team` (`id_team`,`id_task`,`answer_str`),
  KEY `id_team_2` (`id_team`),
  KEY `id_task` (`id_task`),
  CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `answer_ibfk_2` FOREIGN KEY (`id_task`) REFERENCES `task` (`id_task`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='pokusy uhadnout kod ukolu';


DROP TABLE IF EXISTS `chat`;
CREATE TABLE `chat` (
  `id_chat` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_team` int(25) unsigned NOT NULL COMMENT 'tym, ktery prispevek vlozil',
  `content` text COLLATE utf8_czech_ci NOT NULL COMMENT 'text prispevku',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_chat`),
  KEY `id_team` (`id_team`),
  CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='diskusni prispevku na chatu';


DROP TABLE IF EXISTS `competitor`;
CREATE TABLE `competitor` (
  `id_competitor` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_team` int(25) unsigned NOT NULL COMMENT 'tym, do ktereho ucastnik patri',
  `id_school` int(25) unsigned DEFAULT NULL COMMENT 'skola, kam ucastnik chodi',
  `name` varchar(250) COLLATE utf8_czech_ci NOT NULL COMMENT 'jmeno',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_competitor`),
  KEY `id_team` (`id_team`),
  KEY `id_school` (`id_school`),
  CONSTRAINT `competitor_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `competitor_ibfk_2` FOREIGN KEY (`id_school`) REFERENCES `school` (`id_school`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='informace o soutezicich';


DROP TABLE IF EXISTS `group`;
CREATE TABLE `group` (
  `id_group` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_year` int(25) unsigned NOT NULL,
  `to_show` datetime NOT NULL COMMENT 'cas. kdy ma byt skupina zverejnena',
  `type` enum('set','serie') COLLATE utf8_czech_ci NOT NULL COMMENT 'zpristupnovani uloh; set: vse najednou, serie: po vyreseni ukolu',
  `code_name` varchar(5) COLLATE utf8_czech_ci NOT NULL COMMENT 'kratky slovni identifikator skupiny uloh',
  `text` text COLLATE utf8_czech_ci NOT NULL COMMENT 'komentar k serii, ktery muze napr. obsahovat odkaz ke stazeni pdf apod.',
  `allow_zeroes` tinyint(1) NOT NULL COMMENT 'davat nulu za mnozstvi pokusu',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_group`),
  KEY `id_year` (`id_year`),
  CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_year`) REFERENCES `year` (`id_year`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='serie ukolu';


DROP TABLE IF EXISTS `group_state`;
CREATE TABLE `group_state` (
  `id_group` int(25) unsigned NOT NULL,
  `id_team` int(25) unsigned NOT NULL,
  `task_counter` int(4) DEFAULT NULL COMMENT 'počet vydaných úloh ze série',
  PRIMARY KEY (`id_group`,`id_team`),
  KEY `id_group` (`id_group`),
  KEY `id_team` (`id_team`),
  CONSTRAINT `group_state_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`),
  CONSTRAINT `group_state_ibfk_2` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='stav týmu v rámci série';


DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `id_log` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_team` int(25) unsigned NOT NULL COMMENT 'tym, ktereho se zaznam tyka',
  `type` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'typ zaznamu',
  `text` text COLLATE utf8_czech_ci NOT NULL COMMENT 'text zaznamu',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  PRIMARY KEY (`id_log`),
  KEY `id_team` (`id_team`),
  CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='logovani akci tymu';


DROP TABLE IF EXISTS `period`;
CREATE TABLE `period` (
  `id_period` int(25) NOT NULL AUTO_INCREMENT,
  `id_group` int(25) unsigned NOT NULL,
  `begin` datetime NOT NULL COMMENT 'začátek období',
  `end` datetime NOT NULL COMMENT 'konec období',
  `allow_skip` tinyint(1) NOT NULL COMMENT 'umožnit přeskočení úlohy a zisk další ze skupiny',
  `time_penalty` int(4) NOT NULL COMMENT 'počet trestných sekund za špatnou odpověď',
  `reserve_size` int(4) NOT NULL COMMENT 'počet úloh vydaných k řešení navíc',
  PRIMARY KEY (`id_period`),
  KEY `id_group` (`id_group`),
  CONSTRAINT `period_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='období pro odevzdávání série úloh';


DROP TABLE IF EXISTS `school`;
CREATE TABLE `school` (
  `id_school` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `name` varchar(150) COLLATE utf8_czech_ci NOT NULL COMMENT 'nazev skoly',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_school`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Skoly, ze kterych pochazi soutezici';


DROP TABLE IF EXISTS `task`;
CREATE TABLE `task` (
  `id_task` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_group` int(25) unsigned NOT NULL COMMENT 'skupina, do ktere ukol patri',
  `number` int(2) unsigned NOT NULL COMMENT 'cislo ukolu v ramci serie',
  `name` varchar(250) COLLATE utf8_czech_ci NOT NULL COMMENT 'nazev ukolu',
  `filename` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'neuhodnutelny nazev souboru se zadanim ukolu',
  `points` int(2) unsigned NOT NULL COMMENT 'plny pocet bodu za ulohu',
  `answer_type` enum('str','int','real') COLLATE utf8_czech_ci NOT NULL COMMENT 'datovy typ vysledku ukolu',
  `answer_str` varchar(250) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'sloupec pro retezcovou odpoved',
  `answer_int` int(25) DEFAULT NULL COMMENT 'sloupec pro celociselnou odpoved',
  `answer_real` double DEFAULT NULL COMMENT 'sloupec pro realnou odpoved',
  `real_tolerance` double DEFAULT NULL COMMENT 'povolena odchylka u realnych odpovedi',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_task`),
  KEY `id_serie` (`id_group`),
  KEY `number` (`number`),
  CONSTRAINT `task_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='ukoly';


DROP TABLE IF EXISTS `task_state`;
CREATE TABLE `task_state` (
  `id_task` int(25) unsigned NOT NULL,
  `id_team` int(25) unsigned NOT NULL,
  `skipped` tinyint(1) NOT NULL COMMENT 'úloha byla přeskočena',
  `substitute` tinyint(1) NOT NULL COMMENT 'úloha vydána jako náhrada při přeskakování',
  PRIMARY KEY (`id_task`,`id_team`),
  KEY `id_task` (`id_task`),
  KEY `id_team` (`id_team`),
  CONSTRAINT `task_state_ibfk_1` FOREIGN KEY (`id_task`) REFERENCES `task` (`id_task`),
  CONSTRAINT `task_state_ibfk_2` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='stav úkolu pro daný tým';


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team` (
  `id_team` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `id_year` int(25) unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8_czech_ci NOT NULL COMMENT 'prihlasovaci jmeno',
  `password` varchar(160) COLLATE utf8_czech_ci NOT NULL COMMENT 'zahashovane heslo',
  `category` enum('high_school','open') COLLATE utf8_czech_ci NOT NULL COMMENT 'soutezni kategorie',
  `email` varchar(150) COLLATE utf8_czech_ci DEFAULT NOT NULL COMMENT 'e-mailova adresa',
  `address` text COLLATE utf8_czech_ci NOT NULL COMMENT 'kontaktni adresa',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_team`),
  UNIQUE KEY `id_year` (`id_year`,`name`),
  UNIQUE KEY `id_year_2` (`id_year`,`email`),
  CONSTRAINT `team_ibfk_1` FOREIGN KEY (`id_year`) REFERENCES `year` (`id_year`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Soutezni tymy';


DROP TABLE IF EXISTS `year`;
CREATE TABLE `year` (
  `id_year` int(25) unsigned NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
  `name` varchar(50) COLLATE utf8_czech_ci NOT NULL,
  `registration_start` datetime NOT NULL COMMENT 'cas, kdy zacina registrace do tohoto rocniku',
  `registration_end` datetime NOT NULL COMMENT 'cas, kdy konci registrace do tohoto rocniku',
  `game_start` datetime NOT NULL COMMENT 'cas, kdy zacina hra',
  `game_end` datetime NOT NULL COMMENT 'cas, kdy konci hra',
  `inserted` datetime NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
  PRIMARY KEY (`id_year`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='Rocniky';

