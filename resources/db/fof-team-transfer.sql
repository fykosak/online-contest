-- Creates two minimalistic tables team_fof and competitor_fof.
-- Export only data from those tables.
-- Remove the _fof suffix from .sql file
-- Import into the game system.

SET @eventId = 152;

START TRANSACTION;
SET foreign_key_checks = 0;

DROP TABLE IF EXISTS `team_fof`;
CREATE TABLE `team_fof`
(
    `id_team`      int(25) unsigned                                  NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `name`         varchar(150) COLLATE utf8_czech_ci                NOT NULL COMMENT 'prihlasovaci jmeno',
    `password`     varchar(160) COLLATE utf8_czech_ci                NOT NULL COMMENT 'zahashovane heslo',
    `category`     enum ('hs_a','hs_b','hs_c') COLLATE utf8_czech_ci NOT NULL COMMENT 'soutezni kategorie',
    `email`        varchar(150) COLLATE utf8_czech_ci                NOT NULL COMMENT 'e-mailova adresa',
    `address`      text COLLATE utf8_czech_ci                        NOT NULL COMMENT 'kontaktni adresa',
    `disqualified` tinyint(1)                                        NOT NULL COMMENT 'tym diskvalifikovan',
    `inserted`     datetime                                          NOT NULL COMMENT 'cas, kdy byla polozka vlozena do systemu',
    PRIMARY KEY (`id_team`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT into team_fof(id_team, name, password, category, email, address, disqualified, inserted)
SELECT id_team, name, password, category, email, address, disqualified, inserted from
    (
        SELECT t.e_fyziklani_team_id                   as `id_team`,
               t.name                                  As `name`,
               IF(t.password IS NULL, "deadbeef", SHA1(t.password)) as `password`,
               CASE t.category
                   WHEN 'A'
                       THEN 'hs_a'
                   WHEN 'B'
                       THEN 'hs_b'
                   WHEN 'C'
                       THEN 'hs_c'
                   END                                 as `category`,
               ""                                      as `email`,
               ""                                      as `address`,
               IF(t.status = 'disqualified', '1', '0') as `disqualified`,
               t.created                               as `inserted`
        FROM fksdb.e_fyziklani_team t
        WHERE t.status != 'cancelled'
          AND t.event_id = @eventId
    ) view_team;


DROP TABLE IF EXISTS `competitor_fof`;
CREATE TABLE `competitor_fof`
(
    `id_competitor` int(25) unsigned                   NOT NULL AUTO_INCREMENT COMMENT 'identifikator',
    `id_team`       int(25) unsigned                   NOT NULL COMMENT 'tym, do ktereho ucastnik patri',
    `name`          varchar(250) COLLATE utf8_czech_ci NOT NULL COMMENT 'jmeno',
    `email`         varchar(150) COLLATE utf8_czech_ci          DEFAULT NULL,
    PRIMARY KEY (`id_competitor`),
    KEY `id_team` (`id_team`),
    CONSTRAINT `competitor_fof_ibfk_1` FOREIGN KEY (`id_team`) REFERENCES `team_fof` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8;

INSERT into competitor_fof (id_competitor, id_team, name, email)
SELECT ep.event_participant_id, efp.e_fyziklani_team_id, if(`p`.`display_name` is null,concat(`p`.`other_name`,' ',`p`.`family_name`),`p`.`display_name`), pi.email
FROM fksdb.event_participant ep
         LEFT JOIN fksdb.e_fyziklani_participant efp ON efp.event_participant_id = ep.event_participant_id
         LEFT JOIN fksdb.e_fyziklani_team eft ON eft.e_fyziklani_team_id = efp.e_fyziklani_team_id
         LEFT JOIN fksdb.person_info pi ON pi.person_id = ep.person_id
         LEFT JOIN fksdb.person p ON p.person_id = ep.person_id
WHERE eft.status != 'cancelled'
  AND eft.event_id = @eventId;

COMMIT;
