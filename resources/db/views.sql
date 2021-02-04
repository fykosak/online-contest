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
