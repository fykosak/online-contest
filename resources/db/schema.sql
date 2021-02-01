-- Adminer 4.2.0 MySQL dump

SET NAMES utf8;
SET foreign_key_checks = 0;
SET time_zone = 'SYSTEM';
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `answer`;
CREATE TABLE `answer`
(
    `id_answer`     int(25) unsigned    NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_team`       int(25) unsigned    NOT NULL COMMENT 'tym, ktery hada kod',
    `id_task`       int(25) unsigned    NOT NULL COMMENT 'ukol, jehoz kod se hada',
    `answer_str`    varchar(250) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'sloupec pro retezcovou odpoved',
    `answer_int`    int(25)                            DEFAULT NULL COMMENT 'sloupec pro celociselnou odpoved',
    `answer_real`   double                             DEFAULT NULL COMMENT 'sloupec pro realnou odpoved',
    `correct`       tinyint(1) unsigned NOT NULL       DEFAULT '0' COMMENT 'je odpoved spravna',
    `inserted`      datetime            NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`       timestamp           NOT NULL       DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    `double_points` BOOL                NOT NULL       DEFAULT FALSE COMMENT 'Was used double points card?',
    PRIMARY KEY (`id_answer`),
    KEY `id_team_2` (`id_team`),
    KEY `id_task` (`id_task`),
    CONSTRAINT `answer_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `answer_ibfk_2` FOREIGN KEY (`id_task`) REFERENCES `task` (`id_task`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='pokusy uhadnout kod ukolu';


DROP TABLE IF EXISTS `chat`;
CREATE TABLE `chat`
(
    `id_chat`   int(25) unsigned                       NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_parent` int(25) unsigned                                DEFAULT NULL COMMENT 'identifikator rodicovskeho prispevku',
    `id_team`   int(25) unsigned                                DEFAULT NULL COMMENT 'tym, ktery prispevek vlozil',
    `org`       tinyint(1) unsigned                    NOT NULL DEFAULT '0' COMMENT 'organizatorsky prispevek',
    `content`   text COLLATE utf8_czech_ci             NOT NULL COMMENT 'text prispevku',
    `lang`      enum ('cs','en') COLLATE utf8_czech_ci NOT NULL COMMENT 'jazyk fora',
    `inserted`  datetime                               NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`   timestamp                              NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_chat`),
    KEY `id_team` (`id_team`),
    KEY `id_parent` (`id_parent`),
    CONSTRAINT `chat_ibfk_2` FOREIGN KEY (`id_parent`) REFERENCES `chat` (`id_chat`) ON DELETE SET NULL,
    CONSTRAINT `chat_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='diskusni prispevky na chatu';


DROP TABLE IF EXISTS `competitor`;
CREATE TABLE `competitor`
(
    `id_competitor` int(25) unsigned                   NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_team`       int(25) unsigned                   NOT NULL COMMENT 'tym, do ktereho ucastnik patri',
    `id_school`     int(25) unsigned                            DEFAULT NULL COMMENT 'skola, kam ucastnik chodi',
    `name`          varchar(250) COLLATE utf8_czech_ci NOT NULL COMMENT 'jmeno',
    `email`         varchar(150) COLLATE utf8_czech_ci          DEFAULT NULL,
    `study_year`    tinyint(4)                                  DEFAULT NULL COMMENT 'rocnik, ktery studuje, viz TeamFormComponent',
    `inserted`      datetime                           NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`       timestamp                          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_competitor`),
    KEY `id_team` (`id_team`),
    KEY `id_school` (`id_school`),
    CONSTRAINT `competitor_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `competitor_ibfk_2` FOREIGN KEY (`id_school`) REFERENCES `school` (`id_school`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='informace o soutezicich';


DROP TABLE IF EXISTS `group`;
CREATE TABLE `group`
(
    `id_group`     int(25) unsigned                           NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_year`      int(25) unsigned                           NOT NULL,
    `to_show`      datetime                                   NOT NULL COMMENT 'cas. kdy ma byt skupina zverejnena',
    `type`         enum ('set','serie') COLLATE utf8_czech_ci NOT NULL COMMENT 'zpristupnovani uloh; set: vse najednou, serie: po vyreseni ukolu',
    `code_name`    varchar(5) COLLATE utf8_czech_ci           NOT NULL COMMENT 'kratky slovni identifikator skupiny uloh',
    `text_cs`         text COLLATE utf8_czech_ci                 NOT NULL COMMENT 'komentar k serii, ktery muze napr. obsahovat odkaz ke stazeni pdf apod.',
    `text_en`         text COLLATE utf8_czech_ci                 NOT NULL COMMENT 'komentar k serii, ktery muze napr. obsahovat odkaz ke stazeni pdf apod.',
    `allow_zeroes` tinyint(1)                                 NOT NULL COMMENT 'davat nulu za mnozstvi pokusu',
    `inserted`     datetime                                   NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`      timestamp                                  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_group`),
    KEY `id_year` (`id_year`),
    CONSTRAINT `group_ibfk_1` FOREIGN KEY (`id_year`) REFERENCES `year` (`id_year`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='serie ukolu';


DROP TABLE IF EXISTS `group_state`;
CREATE TABLE `group_state`
(
    `id_group`     int(25) unsigned NOT NULL,
    `id_team`      int(25) unsigned NOT NULL,
    `task_counter` int(4) DEFAULT NULL COMMENT 'počet vydaných úloh ze série',
    PRIMARY KEY (`id_group`, `id_team`),
    KEY `id_group` (`id_group`),
    KEY `id_team` (`id_team`),
    CONSTRAINT `group_state_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`),
    CONSTRAINT `group_state_ibfk_2` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='stav týmu v rámci série';


DROP TABLE IF EXISTS `log`;
CREATE TABLE `log`
(
    `id_log`   int(25) unsigned           NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_team`  int(25) unsigned                   DEFAULT NULL COMMENT 'tym, ktereho se zaznam tyka',
    `type`     varchar(250) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'typ zaznamu',
    `text`     text COLLATE utf8_czech_ci NOT NULL COMMENT 'text zaznamu',
    `inserted` datetime                   NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    PRIMARY KEY (`id_log`),
    KEY `id_team` (`id_team`),
    CONSTRAINT `log_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='logovani akci tymu';


DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification`
(
    `notification_id` int(25)                                NOT NULL AUTO_INCREMENT,
    `message`         text COLLATE utf8_czech_ci             NOT NULL COMMENT 'text notifikace',
    `lang`            enum ('cs','en') COLLATE utf8_czech_ci NOT NULL COMMENT 'jazyk notifikace',
    `created`         timestamp                              NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka vlozena do systemu',
    PRIMARY KEY (`notification_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='notifikace na nastenku';


DROP TABLE IF EXISTS `period`;
CREATE TABLE `period`
(
    `id_period`    int(25)          NOT NULL AUTO_INCREMENT,
    `id_group`     int(25) unsigned NOT NULL,
    `begin`        datetime         NOT NULL COMMENT 'začátek období',
    `end`          datetime         NOT NULL COMMENT 'konec období',
    `allow_skip`   tinyint(1)       NOT NULL COMMENT 'umožnit přeskočení úlohy a zisk další ze skupiny',
    `has_bonus`    tinyint(1)       NOT NULL COMMENT 'při odevzdání úlohy v této periodě se započítá Hurry up bonus',
    `time_penalty` int(4)           NOT NULL COMMENT 'počet trestných sekund za špatnou odpověď',
    `reserve_size` int(4)           NOT NULL COMMENT 'počet úloh vydaných k řešení navíc',
    PRIMARY KEY (`id_period`),
    KEY `id_group` (`id_group`),
    CONSTRAINT `period_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='období pro odevzdávání série úloh';


DROP TABLE IF EXISTS `school`;
CREATE TABLE `school`
(
    `id_school`   int(25) unsigned                   NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `name`        varchar(150) COLLATE utf8_czech_ci NOT NULL COMMENT 'nazev skoly',
    `country_iso` char(2) COLLATE utf8_general_ci    NOT NULL COMMENT 'ISO 3166-1 statu skoly',
    `inserted`    datetime                           NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`     timestamp                          NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_school`),
    UNIQUE KEY `name` (`name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='Skoly, ze kterych pochazi soutezici';


DROP TABLE IF EXISTS `task`;
CREATE TABLE `task`
(
    `id_task`         int(25) unsigned                                 NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_group`        int(25) unsigned                                 NOT NULL COMMENT 'skupina, do ktere ukol patri',
    `number`          int(2) unsigned                                  NOT NULL COMMENT 'cislo ukolu v ramci serie',
    `name_cs`         varchar(250) COLLATE utf8_czech_ci               NOT NULL COMMENT 'nazev ukolu',
    `name_en`         varchar(250) CHARACTER SET utf8                  NOT NULL COMMENT 'nazev ukolu',
    `filename_cs`     varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'neuhodnutelny nazev souboru se zadanim ukolu',
    `filename_en`     varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL COMMENT 'neuhodnutelny nazev souboru se zadanim ukolu',
    `points`          int(2) unsigned                                  NOT NULL COMMENT 'plny pocet bodu za ulohu',
    `cancelled`       tinyint(4)                                       NOT NULL DEFAULT '0' COMMENT 'uloha zrusena v prubehu hry',
    `answer_factory`  TEXT                                             NULL     DEFAULT NULL COLLATE utf8_czech_ci DEFAULT NULL COMMENT '',
    `answer_type`     enum ('str','int','real') COLLATE utf8_czech_ci  NOT NULL COMMENT 'datovy typ vysledku ukolu',
    `answer_str`      varchar(250) COLLATE utf8_czech_ci                        DEFAULT NULL COMMENT 'sloupec pro retezcovou odpoved',
    `answer_int`      int(25)                                                   DEFAULT NULL COMMENT 'sloupec pro celociselnou odpoved',
    `answer_real`     double                                                    DEFAULT NULL COMMENT 'sloupec pro realnou odpoved',
    `answer_unit`     varchar(255) COLLATE utf8_czech_ci                        DEFAULT NULL COMMENT 'jednotka vysledku, HTML fragment',
    `real_tolerance`  double                                                    DEFAULT NULL COMMENT 'povolena odchylka u realnych odpovedi',
    `real_sig_digits` tinyint(4)                                                DEFAULT NULL COMMENT 'ocekavany pocet platnych cifer vysledku',
    `inserted`        datetime                                         NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`         timestamp                                        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_task`),
    KEY `id_serie` (`id_group`),
    KEY `number` (`number`),
    KEY `cancelled` (`cancelled`),
    CONSTRAINT `task_ibfk_1` FOREIGN KEY (`id_group`) REFERENCES `group` (`id_group`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='ukoly';


DROP TABLE IF EXISTS `task_state`;
CREATE TABLE `task_state`
(
    `id_task`    int(25) unsigned           NOT NULL,
    `id_team`    int(25) unsigned           NOT NULL,
    `skipped`    tinyint(1)                 NOT NULL COMMENT 'úloha byla přeskočena',
    `substitute` tinyint(1)       DEFAULT 0 NOT NULL COMMENT 'úloha vydána jako náhrada při přeskakování (not used)',
    `points`     int(25) unsigned DEFAULT NULL COMMENT 'body za úlohu (bez bonusu)',
    `inserted`   datetime                   NOT NULL COMMENT 'cas vlozeni zaznamu',
    PRIMARY KEY (`id_task`, `id_team`),
    KEY `id_task` (`id_task`),
    KEY `id_team` (`id_team`),
    CONSTRAINT `task_state_ibfk_1` FOREIGN KEY (`id_task`) REFERENCES `task` (`id_task`),
    CONSTRAINT `task_state_ibfk_2` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='stav úkolu pro daný tým';


DROP TABLE IF EXISTS `team`;
CREATE TABLE `team`
(
    `id_team`      int(25) unsigned                                                                NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_year`      int(25) unsigned                                                                NOT NULL,
    `name`         varchar(150) COLLATE utf8_czech_ci                                              NOT NULL COMMENT 'prihlasovaci jmeno',
    `password`     varchar(160) COLLATE utf8_czech_ci                                              NOT NULL COMMENT 'zahashovane heslo',
    `category`     enum ('high_school','open','abroad','hs_a','hs_b','hs_c') COLLATE utf8_czech_ci NOT NULL COMMENT 'soutezni kategorie',
    `email`        varchar(150) COLLATE utf8_czech_ci                                              NOT NULL COMMENT 'e-mailova adresa',
    `address`      text COLLATE utf8_czech_ci                                                      NOT NULL COMMENT 'kontaktni adresa',
    `disqualified` tinyint(1)                                                                      NOT NULL COMMENT 'tym diskvalifikovan',
    `inserted`     datetime                                                                        NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`      timestamp                                                                       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    `score_exp`    int(25)                                                                         NOT NULL DEFAULT '0' COMMENT 'zive skore, experimental hotfix feature',
    PRIMARY KEY (`id_team`),
    UNIQUE KEY `id_year` (`id_year`, `name`),
    CONSTRAINT `team_ibfk_1` FOREIGN KEY (`id_year`) REFERENCES `year` (`id_year`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='Soutezni tymy';

DROP TABLE IF EXISTS `token`;
CREATE TABLE `token`
(
    `id_token`   int(25) unsigned                 NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_team`    int(25) unsigned                 NOT NULL,
    `token`      char(10) COLLATE utf8_general_ci NOT NULL COMMENT 'token',
    `not_before` datetime                         NOT NULL COMMENT 'cas zacatku platnosti zaznamu',
    `not_after`  datetime                         NOT NULL COMMENT 'cas expirace zaznamu',
    PRIMARY KEY (`id_token`),
    UNIQUE KEY `token` (`token`),
    CONSTRAINT `token_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='Tokeny pro reset hesla';


DROP TABLE IF EXISTS `year`;
CREATE TABLE `year`
(
    `id_year`            int(25) unsigned                  NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `name`               varchar(50) COLLATE utf8_czech_ci NOT NULL,
    `registration_start` datetime                          NOT NULL COMMENT 'cas, kdy zacina registrace do tohoto rocniku',
    `registration_end`   datetime                          NOT NULL COMMENT 'cas, kdy konci registrace do tohoto rocniku',
    `game_start`         datetime                          NOT NULL COMMENT 'cas, kdy zacina hra',
    `game_end`           datetime                          NOT NULL COMMENT 'cas, kdy konci hra',
    `inserted`           datetime                          NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    `updated`            timestamp                         NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'cas, kdy byla polozka naposledy zmenena',
    PRIMARY KEY (`id_year`),
    UNIQUE KEY `name` (`name`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci COMMENT ='Rocniky';

DROP TABLE IF EXISTS `rating`;
CREATE TABLE `rating`
(
    `rating_id` INT(11) PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `team_id`   INT(25) unsigned    NOT NULL,
    `task_id`   INT(25) unsigned    NOT NULL,
    `rating`    INT(8)              NULL DEFAULT NULL,
    # todo add more options
    CONSTRAINT `rating_team1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id_team`),
    CONSTRAINT `rating_task1` FOREIGN KEY (`task_id`) REFERENCES `task` (`id_task`),
    UNIQUE KEY `id_year` (`team_id`, `task_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;

DROP TABLE IF EXISTS `card_usage`;
CREATE TABLE `card_usage`
(
    `card_usage_id` INT(11) PRIMARY KEY                                               NOT NULL AUTO_INCREMENT,
    `card_type`     ENUM ('skip','reset','double_points','add_task','hint','options') NOT NULL,
    `team_id`       INT(25) UNSIGNED                                                  NOT NULL,
    `created`       TIMESTAMP                                                         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `data`          VARCHAR(256)                                                      NULL     DEFAULT NULL COMMENT 'serialized data',
    INDEX (`team_id`),
    INDEX (`card_type`),
    UNIQUE (`team_id`, `card_type`),
    CONSTRAINT `fk_card_usage_team` FOREIGN KEY (`team_id`) REFERENCES `team` (`id_team`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;


DROP TABLE IF EXISTS `task_hint`;
CREATE TABLE `task_hint`
(
    `task_id` int(25) UNSIGNED NOT NULL PRIMARY KEY,
    `hint_cs` TEXT,
    `hint_en` TEXT,
    CONSTRAINT `fk_task_hint_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`id_task`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;

DROP TABLE IF EXISTS `answer_options`;
CREATE TABLE `answer_options`
(
    `task_id`     int(25) UNSIGNED NOT NULL PRIMARY KEY,
    `option_1_cs` VARCHAR(64),
    `option_1_en` VARCHAR(64),
    `option_2_cs` VARCHAR(64),
    `option_2_en` VARCHAR(64),
    `option_3_cs` VARCHAR(64),
    `option_3_en` VARCHAR(64),
    `option_4_cs` VARCHAR(64),
    `option_4_en` VARCHAR(64),
    CONSTRAINT `fk_answer_options_task` FOREIGN KEY (`task_id`) REFERENCES `task` (`id_task`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  COLLATE = utf8_czech_ci;
