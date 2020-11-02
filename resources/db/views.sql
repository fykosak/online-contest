--
-- V definicích pohledů view_bonus_help, view_bonus a view_bonus_cached je třeba upravit série, které tvoří hurry up sérii
-- TODO doimplementovat chování pro skupiny typu 'set' (skipped příznak etc.)
--

DROP VIEW IF EXISTS `view_current_year`;
CREATE VIEW `view_current_year` AS
    SELECT *
    FROM `year`
    ORDER BY `id_year` DESC
    LIMIT 1;

DROP VIEW IF EXISTS `view_team`;
CREATE VIEW `view_team` AS
    SELECT `team`.*
    FROM `team`
    INNER JOIN `view_current_year` USING(`id_year`)
    ORDER BY `category`, `inserted`;

DROP VIEW IF EXISTS `view_group`;
CREATE VIEW `view_group` AS
    SELECT `group`.*
    FROM `group`
    INNER JOIN `view_current_year` USING(`id_year`);

DROP VIEW IF EXISTS `view_competitor`;
CREATE VIEW `view_competitor` AS
SELECT `competitor`.`id_competitor` AS `id_competitor`,`competitor`.`id_team` AS `id_team`,`competitor`.`id_school` AS `id_school`,`competitor`.`name` AS `name`,`competitor`.`email` AS `email`, `competitor`.`study_year` AS `study_year`, `competitor`.`inserted` AS `inserted`,`competitor`.`updated` AS `updated`,`school`.`name` AS `school_name`,`school`.`country_iso` AS `country_iso`,`team`.`name` AS `team_name`,`team`.`category` AS `category` from ((`competitor` join `team` on((`competitor`.`id_team` = `team`.`id_team`))) left join `school` on((`competitor`.`id_school` = `school`.`id_school`))) order by `team`.`category`,`team`.`name`,`competitor`.`name`;

DROP VIEW IF EXISTS `view_task`;
CREATE VIEW `view_task` AS
	SELECT
		`task`.*,
                CONCAT(`group`.`code_name`, `task`.`number`) AS `code_name`,
                `group`.`to_show` AS `group_to_show`,
                `group`.`type` AS `group_type`
	FROM `task`
	INNER JOIN `group` USING(`id_group`)
	INNER JOIN `view_current_year` USING(`id_year`)
	ORDER BY `task`.`id_group`, `task`.`number`;

-- úlohy přístupné týmu jako zadání
-- stornované úlohy jsou takto dostupné (stejně si je mohl už někdo stáhnout)
-- TODO matoucí, zrušit
DROP VIEW IF EXISTS `view_available_task`;
CREATE VIEW `view_available_task` AS
	SELECT
                `view_team`.`id_team`,
		`view_task`.*
	FROM (`view_task`, `view_team`)
        LEFT JOIN `group_state` USING (`id_group`, `id_team`)
	WHERE `group_to_show` <= NOW()
              AND (
                (`group_type` = 'serie' AND `view_task`.`number` <= `group_state`.`task_counter`)
                OR (`group_type` = 'set')
              )
	ORDER BY `view_task`.`id_group`, `view_task`.`number`;

-- úlohy přístupné týmu pro odeslání zadání
DROP VIEW IF EXISTS `view_submit_available_task`;
CREATE VIEW `view_submit_available_task` AS
	SELECT
		`view_task`.*
	FROM `view_available_task` AS `view_task`
        LEFT JOIN `task_state` USING (`id_task`, `id_team`)
        RIGHT JOIN `period` ON
            `period`.`id_group` = `view_task`.`id_group`
            AND `period`.`begin` <= NOW()
            AND `period`.`end` > NOW()
        WHERE (`task_state`.`skipped` IS NULL OR `task_state`.`skipped` != 1)
            AND `view_task`.`cancelled` = 0
	ORDER BY `view_task`.`id_group`, `view_task`.`number`;

-- úlohy potenciálně přístupné všem (pro účely statistik úkolů)
DROP VIEW IF EXISTS `view_possibly_available_task`;
CREATE VIEW `view_possibly_available_task` AS
	SELECT
		`view_task`.*
	FROM `view_task`
	WHERE `group_to_show` <= NOW() AND `number` <= (SELECT MAX(`task_counter`) FROM `group_state` gs WHERE gs.`id_group` = id_group)
	ORDER BY `view_task`.`id_group`, `view_task`.`number`;

DROP VIEW IF EXISTS `view_answer`;
CREATE VIEW `view_answer` AS
    SELECT `answer`.*
    FROM `answer`
    INNER JOIN `task` USING(`id_task`)
    INNER JOIN `group` USING(`id_group`)
    INNER JOIN `view_current_year` USING(`id_year`);

DROP VIEW IF EXISTS `view_seemingly_correct_answer`;
CREATE VIEW `view_seemingly_correct_answer` AS
	SELECT
		`answer`.*,
                `view_task`.`cancelled`
	FROM `view_answer` AS `answer`
	INNER JOIN `view_task` USING(`id_task`)
	WHERE `answer`.`correct` = 1;

DROP VIEW IF EXISTS `view_correct_answer`;
CREATE VIEW `view_correct_answer` AS
	SELECT
		`answer`.*
	FROM `view_seemingly_correct_answer` AS `answer`
	WHERE 
            `cancelled` = 0;

DROP VIEW IF EXISTS `view_last_correct_answer`;
CREATE VIEW `view_last_correct_answer` AS
	SELECT
                `answer`.`id_team`,
		MAX(`answer`.`inserted`) AS `last_time`
	FROM `view_correct_answer` AS `answer`
	GROUP BY `id_team`;

DROP VIEW IF EXISTS `view_incorrect_answer`;
CREATE VIEW `view_incorrect_answer` AS
	SELECT
		`answer`.*
	FROM `view_answer` AS `answer`
        INNER JOIN `view_task` USING(`id_task`)
	WHERE `view_task`.`cancelled` = 0 AND `answer`.`correct` = 0;

/*
DROP FUNCTION IF EXISTS `task_points_with_discount`;
delimiter //
CREATE FUNCTION `task_points_with_discount`(maximum int(2), allow_zeroes tinyint(1), wrong_tries int(25))
RETURNS int(2)
DETERMINISTIC
BEGIN
DECLARE RetVal int(2);
    IF maximum >= 4 THEN
        SET RetVal =
            CASE wrong_tries
                WHEN 0 THEN maximum
                WHEN 1 THEN CEILING(maximum * 0.6)
                WHEN 2 THEN CEILING(maximum * 0.4)
                WHEN 3 THEN CEILING(maximum * 0.2)
                ELSE 0
            END;
    ELSEIF maximum = 0 THEN
        RETURN 0;
    ELSE
        SET RetVal = maximum - wrong_tries;
    END IF;
    RETURN CASE allow_zeroes
        WHEN 1 THEN GREATEST(0, RetVal)
        WHEN 0 THEN GREATEST(1, RetVal)
    END;
END//
delimiter ;



 DROP VIEW IF EXISTS `view_task_result`;
 CREATE VIEW `view_task_result` AS
 	SELECT
 		`view_task`.`id_team`,
 		`view_task`.`id_task`,
 		`answer`.`inserted`,
 		(
 			IF(
 				`answer`.`inserted` IS NULL,
 				0,
 				task_points_with_discount(
                                    `view_task`.`points`,
                                    `view_group`.`allow_zeroes`,
                                    (SELECT COUNT(1)
                                     FROM `view_incorrect_answer` AS `wrong`
                                     WHERE `wrong`.`id_team` = `view_task`.`id_team` AND `wrong`.`id_task` = `view_task`.`id_task`
                                    )
                                )
 			)
 		) AS `score`
 	FROM (`view_available_task` AS `view_task`)
 	LEFT JOIN `view_correct_answer` AS `answer` USING(`id_task`, `id_team`)
        LEFT JOIN `view_group` USING(`id_group`);
*/

DROP VIEW IF EXISTS `view_bonus_help`;
CREATE VIEW `view_bonus_help` AS
    SELECT
        `team`.`id_team`,
        `view_task`.`number`,
        COUNT(`view_correct_answer`.`id_task`) AS `complete`
    FROM `view_team` AS `team`
    LEFT JOIN `view_correct_answer` USING (`id_team`)
    LEFT JOIN `view_task` USING (`id_task`)
    LEFT JOIN `period` ON
            `period`.`id_group` = `view_task`.`id_group`
            AND `period`.`begin` <= `view_correct_answer`.`inserted`
            AND `period`.`end` > `view_correct_answer`.`inserted`
    WHERE `view_task`.`id_group` IN (2, 3, 4) -- vazba na data, skupiny ke kompletovani (hurry up)
            AND `period`.`has_bonus` = 1
    GROUP BY `id_team`, `number`;

DROP VIEW IF EXISTS `view_bonus`;
CREATE VIEW `view_bonus` AS
    SELECT
        `id_team`,
        SUM(`task_state`.`points`) AS `score`
    FROM `view_bonus_help`
    LEFT JOIN `view_task` ON `view_task`.`number` = `view_bonus_help`.`number` AND `view_task`.`id_group` IN (2, 3, 4)
    LEFT JOIN `task_state` USING (`id_task`, `id_team`)
    WHERE `view_bonus_help`.`complete` = 3  -- vazba na data, skupiny ke kompletovani (hurry up) a jejich počet
    GROUP BY `id_team`;


 DROP VIEW IF EXISTS `view_penality`;
 CREATE VIEW `view_penality` AS
 	SELECT
 		`team`.`id_team`,
 		COUNT(`task_state`.`id_task`) AS `score` -- body dolů za přeskakování
 	FROM `view_team` AS `team`
 	LEFT JOIN `task_state` ON `task_state`.`id_team` = `team`.`id_team` AND `task_state`.`skipped` = 1
        LEFT JOIN `view_task` ON `view_task`.`id_task` = `task_state`.`id_task`
        WHERE `view_task`.`cancelled` = 0
 	GROUP BY `id_team`;


 
 DROP VIEW IF EXISTS `view_total_result`;

DROP VIEW IF EXISTS `view_task_stat`;
CREATE VIEW `view_task_stat` AS
	SELECT 
		`view_possibly_available_task`.*,
		MIN(`view_correct_answer`.`inserted`) AS `best_time`,
		MAX(`view_correct_answer`.`inserted`) AS `worst_time`,
		COUNT(DISTINCT `view_correct_answer`.`id_answer`) AS `count_correct_answer`, -- doesn't make sense for cancelled tasks
		COUNT(DISTINCT `view_answer`.`id_answer`) - COUNT(DISTINCT `view_correct_answer`.`id_answer`) AS `count_incorrect_answer`, -- doesn't make sense for cancelled tasks
		(SELECT COUNT(DISTINCT `task_state`.`id_team`) FROM `task_state` WHERE `task_state`.`id_task` = `view_possibly_available_task`.`id_task` AND `task_state`.`skipped` = 1) AS `count_skipped`,
        (SELECT COUNT(DISTINCT `group_state`.`id_team`) FROM `group_state` WHERE `group_state`.`id_group` = `view_possibly_available_task`.`id_group` AND `group_state`.`task_counter` >= `view_possibly_available_task`.`number`) AS `count_teams_seen`
	FROM `view_possibly_available_task`
	LEFT JOIN `view_answer` USING(`id_task`)
	LEFT JOIN `view_correct_answer` USING(`id_task`,`id_team`)
	GROUP BY `view_possibly_available_task`.`id_task`
	ORDER BY `view_possibly_available_task`.`id_group`, `view_possibly_available_task`.`number`;

--
-- Views that read data from cached tables
--

/*
DROP TABLE IF EXISTS `tmp_task_result`;
CREATE TABLE `tmp_task_result` AS SELECT * FROM `view_task_result`;
*/

DROP TABLE IF EXISTS `tmp_bonus`;
CREATE TABLE `tmp_bonus` AS SELECT * FROM `view_bonus`;

DROP TABLE IF EXISTS `tmp_penality`;
CREATE TABLE `tmp_penality` AS SELECT * FROM `view_penality`;

/*
DROP VIEW IF EXISTS `view_bonus_cached`;
CREATE VIEW `view_bonus_cached` AS
    SELECT
        `id_team`,
        SUM(`tmp_task_result`.`score`) AS `score`
    FROM `view_bonus_help`
    LEFT JOIN `view_task` ON `view_task`.`number` = `view_bonus_help`.`number` AND `view_task`.`id_group` IN (2, 3, 4)
    LEFT JOIN `tmp_task_result` USING (`id_task`, `id_team`)
    WHERE `view_bonus_help`.`complete` = 3  -- vazba na data, skupiny ke kompletovani (hurry up) a jejich počet
    GROUP BY `id_team`;
*/

DROP VIEW IF EXISTS `view_total_result_cached`;
CREATE VIEW `view_total_result_cached` AS
 	SELECT
 		`t`.`id_team`, `t`.`category`, `t`.`disqualified`, `t`.`name`, -- `t`.*,
 		SUM(`ts`.`points`)
                    + IFNULL(`tmp_bonus`.`score`, 0)
                    - IFNULL(`tmp_penality`.`score`, 0) AS `score`,
                IF(
                    (SELECT COUNT(`ts2`.`id_task`) FROM `task_state` `ts2` WHERE `ts2`.`id_team` = `t`.`id_team` AND `ts2`.`skipped` = 1)
                    + (SELECT COUNT(`id_team`) FROM `view_answer` WHERE `id_team` = `t`.`id_team`) > 0
                , 1, 0) AS `activity`,
        `view_last_correct_answer`.`last_time`
 	FROM `view_team` `t`
 	LEFT JOIN `task_state` `ts` ON `ts`.`id_team` = `t`.`id_team`
 	LEFT JOIN `task` ON `ts`.`id_task` = `task`.`id_task`
 	LEFT JOIN `tmp_penality` ON `tmp_penality`.`id_team` = `t`.`id_team`
 	LEFT JOIN `tmp_bonus` ON `tmp_bonus`.`id_team` = `t`.`id_team`
        LEFT JOIN `view_last_correct_answer` ON `view_last_correct_answer`.`id_team` = `t`.`id_team`
        LEFT JOIN `view_task` ON `view_task`.`id_task` = `ts`.`id_task`
        WHERE (`view_task`.`cancelled` = 0 OR `view_task`.`cancelled` IS NULL)
            AND `ts`.`points` > 0
 	GROUP BY `t`.`id_team`
 	ORDER BY `disqualified` ASC, `activity` DESC, `score` DESC,
 	    GROUP_CONCAT(LPAD(`task`.`number`, 2, "0") ORDER BY `task`.`number` DESC) DESC,
 	    GROUP_CONCAT(TIME(`ts`.`inserted`) ORDER BY `ts`.`inserted` DESC) ASC;
 


-- 
--
DROP VIEW IF EXISTS `view_chat`;
CREATE VIEW `view_chat` AS
	SELECT
		`chat`.*,
		`team`.`name` AS `team_name`
	FROM `chat`
	LEFT JOIN `view_team` AS `team` USING(`id_team`)
	ORDER BY `chat`.`inserted` DESC;
