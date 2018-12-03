--
-- !! Important !!
-- UPDATE HERE labels places necessary to manualy update
--

DROP VIEW IF EXISTS `view_fksdb_event`;
CREATE VIEW `view_fksdb_event` AS
    SELECT *
    FROM fksdb.event f
    WHERE f.event_type_id = 9
        AND f.year = 32; -- UPDATE HERE

DROP VIEW IF EXISTS `view_current_year`;
CREATE VIEW `view_current_year` AS
    SELECT
        1 as `id_year`,
        CONCAT(e.event_year, '. ročník') as `name`,
        e.registration_begin as `registration_start`,
        e.registration_end as `registration_end`,
        addtime(e.begin, '0 17:00:00') as `game_start`,
        addtime(e.end, '0 19:59:59') as `game_end`,
        NOW() as `inserted`,
        NOW() as `updated`
    FROM view_fksdb_event e
;
    
DROP VIEW IF EXISTS `view_team`;
CREATE VIEW `view_team` AS
    SELECT
        t.e_fyziklani_team_id as `id_team`,
        1 as `id_year`,
        t.name As `name`,
        t.password as `password`,
        CASE t.category
            WHEN 'A' THEN 'hs_a'
            WHEN 'B' THEN 'hs_b'
            WHEN 'C' THEN 'hs_c'
            WHEN 'F' THEN 'abroad'
            WHEN 'O' THEN 'open'
            ELSE NULL
        END as `category`,
        NULL as `email`,
        t.note as `address`,
        IF(t.status = 'disqualified', '1', '0') as `disqualified`,
        t.created as `inserted`,
        null as `updated`,
        '0' as `score_exp`
    FROM fksdb.e_fyziklani_team t
    INNER JOIN view_fksdb_event USING(`event_id`)
    WHERE t.status != 'cancelled'
;

DROP VIEW IF EXISTS `view_competitor`;
CREATE VIEW `view_competitor` AS
    SELECT
        ep.event_participant_id as `id_competitor`,
        efp.e_fyziklani_team_id as `id_team`,
        ph.school_id as `id_school`,
        p.name as `name`,
        p.email as `email`,
        null as `study_year`,
        ep.created as `inserted`,
        null as `updated`,
        s.name_abbrev as `school_name`,
        sr.country_iso as `country_iso`,
        vt.name as `team_name`,
        vt.category as `category`
    FROM fksdb.event_participant ep
    INNER JOIN view_fksdb_event e ON e.event_id = ep.event_id
    LEFT JOIN fksdb.e_fyziklani_participant efp ON efp.event_participant_id = ep.event_participant_id
    LEFT JOIN view_team vt ON vt.id_team = efp.e_fyziklani_team_id
    LEFT JOIN fksdb.v_person p on p.person_id = ep.person_id
    LEFT JOIN fksdb.person_history ph on ph.person_id = ep.person_id AND ph.ac_year = 2018 -- UPDATE HERE
    LEFT JOIN fksdb.school s on s.school_id = ph.school_id
    LEFT JOIN fksdb.address sa on sa.address_id = s.address_id
    LEFT JOIN fksdb.region sr on sr.region_id = sa.region_id
;

DROP PROCEDURE IF EXISTS tmp_drop_foreign_key;

DELIMITER $$

CREATE PROCEDURE tmp_drop_foreign_key(IN tableName VARCHAR(64), IN constraintName VARCHAR(64))
BEGIN
    IF EXISTS(
        SELECT * FROM information_schema.table_constraints
        WHERE 
            table_schema    = DATABASE()     AND
            table_name      = tableName      AND
            constraint_name = constraintName AND
            constraint_type = 'FOREIGN KEY')
    THEN
        SET @query = CONCAT('ALTER TABLE ', tableName, ' DROP FOREIGN KEY ', constraintName, ';');
        PREPARE stmt FROM @query; 
        EXECUTE stmt; 
        DEALLOCATE PREPARE stmt; 
    END IF; 
END$$

DELIMITER ;

/* ========= Modify - Begin. ========= */
CALL tmp_drop_foreign_key('chat', 'chat_ibfk_1');
-- Add CALL statements for any other tables and foreign keys here.
/* ========= Modify - End. =========== */

DROP PROCEDURE tmp_drop_foreign_key;

-- ALTER TABLE `chat`
-- DROP FOREIGN KEY `chat_ibfk_1`;

-- ALTER TABLE `chat`
-- ADD FOREIGN KEY (`id_team`) REFERENCES `team` (`id_team`) ON DELETE CASCADE ON UPDATE CASCADE;
