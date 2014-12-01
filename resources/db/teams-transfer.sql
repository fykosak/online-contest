-- transfer teams from FKSDB to game database
-- usage: necessary to have FKSDB views imported
--        fills tables school, team and competitor

START TRANSACTION;

-- TRUNCATE TABLE `team`;
INSERT into team(id_year, name, password, category, email, address)
SELECT id_year, name, password, category, email, address from `view_team`;

-- TRUNCATE TABLE `school`;
INSERT into school(name)
SELECT DISTINCT school_name from `view_competitor`;

TRUNCATE TABLE `competitor`;
INSERT into competitor(id_team, id_school, name, email, study_year)
SELECT t.id_team, s.id_school, vc.name, vc.email, vc.study_year from `view_competitor` vc
JOIN `team` t ON t.name=vc.team_name
JOIN `school` s ON s.name=vc.school_name COLLATE utf8_czech_ci;

COMMIT;