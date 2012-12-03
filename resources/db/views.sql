--
-- V definicích pohledů view_bonus_help a view_bonus je třeba upravit série, které tvoří hurry up sérii
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
    ORDER BY `category`, `name`;

DROP VIEW IF EXISTS `view_group`;
CREATE VIEW `view_group` AS
    SELECT `group`.*
    FROM `group`
    INNER JOIN `view_current_year` USING(`id_year`);

DROP VIEW IF EXISTS `view_competitor`;
CREATE VIEW `view_competitor` AS
SELECT `competitor`.`id_competitor` AS `id_competitor`,`competitor`.`id_team` AS `id_team`,`competitor`.`id_school` AS `id_school`,`competitor`.`name` AS `name`,`competitor`.`email` AS `email`, `competitor`.`study_year` AS `study_year`, `competitor`.`inserted` AS `inserted`,`competitor`.`updated` AS `updated`,`school`.`name` AS `school_name`,`team`.`name` AS `team_name`,`team`.`category` AS `category` from ((`competitor` join `team` on((`competitor`.`id_team` = `team`.`id_team`))) left join `school` on((`competitor`.`id_school` = `school`.`id_school`))) order by `team`.`category`,`team`.`name`,`competitor`.`name`;

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
        WHERE `task_state`.`skipped` IS NULL OR `task_state`.`skipped` != 1
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

DROP VIEW IF EXISTS `view_correct_answer`;
CREATE VIEW `view_correct_answer` AS
	SELECT
		`answer`.*
	FROM `view_answer` AS `answer`
	INNER JOIN `task` USING(`id_task`)
	WHERE 
		(`task`.`answer_type` = 'str' AND `answer`.`answer_str` = `task`.`answer_str`)
		OR (`task`.`answer_type` = 'int' AND `answer`.`answer_int` = `task`.`answer_int`)
		OR (`task`.`answer_type` = 'real' AND ABS(`answer`.`answer_real` - `task`.`answer_real`) <= `task`.`real_tolerance`)
	GROUP BY `id_answer`;

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
	WHERE `answer`.`id_answer` NOT IN (SELECT `id_answer` FROM `view_correct_answer`);

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

DROP VIEW IF EXISTS `view_bonus_help`;
CREATE VIEW `view_bonus_help` AS
    SELECT
        `team`.`id_team`,
        `view_task`.`number`,
        COUNT(`view_correct_answer`.`id_task`) AS `complete`
    FROM `view_team` AS `team`
    LEFT JOIN `view_correct_answer` USING (`id_team`)
    LEFT JOIN `view_task` USING (`id_task`)
    WHERE `view_task`.`id_group` IN (6, 7, 8) -- vazba na data, skupiny ke kompletovani (hurry up)
    GROUP BY `id_team`, `number`;

DROP VIEW IF EXISTS `view_bonus`;
CREATE VIEW `view_bonus` AS
    SELECT
        `id_team`,
        SUM(`view_task_result`.`score`) AS `score`
    FROM `view_bonus_help`
    LEFT JOIN `view_task` ON `view_task`.`number` = `view_bonus_help`.`number` AND `view_task`.`id_group` IN (6, 7, 8)
    LEFT JOIN `view_task_result` USING (`id_task`, `id_team`)
    WHERE `view_bonus_help`.`complete` = 3  -- vazba na data, skupiny ke kompletovani (hurry up) a jejich počet
    GROUP BY `id_team`;


 DROP VIEW IF EXISTS `view_penality`;
 CREATE VIEW `view_penality` AS
 	SELECT
 		`team`.`id_team`,
 		COUNT(`task_state`.`id_task`) AS `score` -- body dolů za přeskakování
 	FROM `view_team` AS `team`
 	LEFT JOIN `task_state` ON `task_state`.`id_team` = `team`.`id_team` AND `task_state`.`skipped` = 1
 	GROUP BY `id_team`;


 
 DROP VIEW IF EXISTS `view_total_result`;
 CREATE VIEW `view_total_result` AS
 	SELECT
 		`team`.*,
 		SUM(`view_task_result`.`score`) + IFNULL(`view_bonus`.`score`,0) - `view_penality`.`score` AS `score`,
                IF((SELECT COUNT(`id_task`) FROM `task_state` WHERE `id_team` = `team`.`id_team` AND `skipped` = 0)
                + (SELECT COUNT(`id_team`) FROM `view_answer` WHERE `id_team` = `team`.`id_team`) > 0, 1, 0)
                 AS `activity`
 	FROM `view_team` AS `team`
 	LEFT JOIN `view_task_result` USING(`id_team`)
 	LEFT JOIN `view_penality` USING(`id_team`)
 	LEFT JOIN `view_bonus` USING(`id_team`)
        LEFT JOIN `view_last_correct_answer` USING(`id_team`)
 	GROUP BY `id_team`
 	ORDER BY `activity` DESC, `score` DESC, `last_time` ASC;
 
DROP VIEW IF EXISTS `view_task_stat`;
CREATE VIEW `view_task_stat` AS
	SELECT
		`view_possibly_available_task`.*,
		MIN(`view_correct_answer`.`inserted`) AS `best_time`,
		MAX(`view_correct_answer`.`inserted`) AS `worst_time`,
		FROM_UNIXTIME(AVG(UNIX_TIMESTAMP(`view_correct_answer`.`inserted`))) AS `avg_time`,
		COUNT(DISTINCT `view_correct_answer`.`id_answer`) AS `count_correct_answer`,
		IFNULL((SELECT COUNT(`view_incorrect_answer`.`id_answer`) FROM `view_incorrect_answer` WHERE `view_incorrect_answer`.`id_task` = `view_possibly_available_task`.`id_task` GROUP BY `view_incorrect_answer`.`id_task`),0) AS `count_incorrect_answer`,
                COUNT(DISTINCT `task_state`.`id_team`) AS `count_skipped`
	FROM `view_possibly_available_task`
	LEFT JOIN `view_correct_answer` USING(`id_task`)
        LEFT JOIN `task_state` ON `task_state`.`id_task` = `view_possibly_available_task`.`id_task` AND `task_state`.`skipped` = 1
	GROUP BY `view_possibly_available_task`.`id_task`
	ORDER BY `view_possibly_available_task`.`id_group`, `view_possibly_available_task`.`number`;

DROP VIEW IF EXISTS `view_chat`;
CREATE VIEW `view_chat` AS
	SELECT
		`chat`.*,
		`team`.`name` AS `team_name`
	FROM `chat`
	INNER JOIN `view_team` AS `team` USING(`id_team`)
	ORDER BY `chat`.`inserted` DESC;
