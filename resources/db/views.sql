-- úlohy přístupné týmu jako zadání
-- stornované úlohy jsou takto dostupné (stejně si je mohl už někdo stáhnout)
-- TODO matoucí, zrušit
DROP VIEW IF EXISTS `view_available_task`;
CREATE VIEW `view_available_task` AS
SELECT `team`.`id_team`,
       `task`.*
FROM (`task`, `team`)
         INNER JOIN `group` USING (`id_group`)
         LEFT JOIN `group_state` USING (`id_group`, `id_team`)
WHERE `to_show` <= NOW()
  AND (
        (`group`.`type` = 'serie' AND `task`.`number` <= `group_state`.`task_counter`)
        OR (`group`.`type` = 'set')
    )
ORDER BY `task`.`id_group`, `task`.`number`;

-- úlohy přístupné týmu pro odeslání zadání
DROP VIEW IF EXISTS `view_submit_available_task`;
CREATE VIEW `view_submit_available_task` AS
SELECT `view_available_task`.*
FROM `view_available_task`
         LEFT JOIN `task_state` USING (`id_task`, `id_team`)
         RIGHT JOIN `period` ON
        `period`.`id_group` = `view_available_task`.`id_group`
        AND `period`.`begin` <= NOW()
        AND `period`.`end` > NOW()
WHERE (`task_state`.`skipped` IS NULL OR `task_state`.`skipped` != 1)
  AND `view_available_task`.`cancelled` = 0
ORDER BY `view_available_task`.`id_group`, `view_available_task`.`number`;

-- úlohy potenciálně přístupné všem (pro účely statistik úkolů)
DROP VIEW IF EXISTS `view_possibly_available_task`;
CREATE VIEW `view_possibly_available_task` AS
SELECT `task`.*
FROM `task`
         INNER JOIN `group` USING (`id_group`)
WHERE `group`.`to_show` <= NOW()
  AND `number` <= (SELECT MAX(`task_counter`) FROM `group_state` gs WHERE gs.`id_group` = id_group)
ORDER BY `task`.`id_group`, `task`.`number`;


DROP VIEW IF EXISTS `view_total_result`;
CREATE VIEW `view_total_result` AS
SELECT `t`.*,
       SUM(`ts`.`points`)
           + IFNULL(`view_bonus`.`score`, 0) AS `score`,
       IF(
                       (SELECT COUNT(`ts2`.`id_task`)
                        FROM `task_state` `ts2`
                        WHERE `ts2`.`id_team` = `t`.`id_team`
                          AND `ts2`.`skipped` = 1)
                       + (SELECT COUNT(`id_team`) FROM `view_answer` WHERE `id_team` = `t`.`id_team`) > 0
           , 1, 0)                           AS `activity`,
       `view_last_correct_answer`.`last_time`
FROM `team` `t`
         LEFT JOIN `task_state` `ts` ON `ts`.`id_team` = `t`.`id_team`
         LEFT JOIN `view_bonus` ON `t`.`id_team` = `view_bonus`.`id_team`
         LEFT JOIN `view_last_correct_answer` ON `t`.`id_team` = `view_last_correct_answer`.`id_team`
         LEFT JOIN `task` ON `task`.`id_task` = `ts`.`id_task`
WHERE `task`.`cancelled` = 0
   OR `task`.`cancelled` IS NULL
GROUP BY `t`.`id_team`
ORDER BY `disqualified` ASC, `activity` DESC, `score` DESC, `last_time` ASC;

DROP VIEW IF EXISTS `view_task_stat`;
CREATE VIEW `view_task_stat` AS
SELECT `view_possibly_available_task`.*,
       MIN(`view_correct_answer`.`inserted`)                                           AS `best_time`,
       MAX(`view_correct_answer`.`inserted`)                                           AS `worst_time`,
       COUNT(DISTINCT `view_correct_answer`.`id_answer`)                               AS `count_correct_answer`,   -- doesn't make sense for cancelled tasks
       COUNT(DISTINCT `view_answer`.`id_answer`) -
       COUNT(DISTINCT `view_correct_answer`.`id_answer`)                               AS `count_incorrect_answer`, -- doesn't make sense for cancelled tasks
       (SELECT COUNT(DISTINCT `task_state`.`id_team`)
        FROM `task_state`
        WHERE `task_state`.`id_task` = `view_possibly_available_task`.`id_task`
          AND `task_state`.`skipped` = 1)                                              AS `count_skipped`,
       (SELECT COUNT(DISTINCT `group_state`.`id_team`)
        FROM `group_state`
        WHERE `group_state`.`id_group` = `view_possibly_available_task`.`id_group`
          AND `group_state`.`task_counter` >= `view_possibly_available_task`.`number`) AS `count_teams_seen`
FROM `view_possibly_available_task`
         LEFT JOIN `view_answer` USING (`id_task`)
         LEFT JOIN (SELECT `answer`.*
                    FROM `answer`
                             INNER JOIN `task` USING (`id_task`)
                    WHERE `task`.`cancelled` = 0
                      AND `answer`.`correct` = 1
) as `view_correct_answer` USING (`id_task`, `id_team`)
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
CREATE TABLE `tmp_bonus` AS
SELECT *
FROM `view_bonus`;

DROP TABLE IF EXISTS `tmp_penality`;
CREATE TABLE `tmp_penality` AS
SELECT *
FROM `view_penality`;
