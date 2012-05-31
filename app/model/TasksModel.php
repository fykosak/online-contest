<?php

class TasksModel extends AbstractModel {
    const TYPE_STR = 'str';
    const TYPE_INT = 'int';
    const TYPE_REAL = 'real';

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_task] = %i", $id)->fetch();
    }

    /**
     * @return DibiDataSource
     */
    public function findAll() {
        return $this->getConnection()->dataSource("SELECT * FROM [view_task]");
    }

    /**
     * @return DibiDataSource
     */
    public function findPossiblyAvailable($teamId = NULL) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_possibly_available_task]");
        return $source;
    }

    /**
     * @return DibiDataSource
     */
    public function findProblemAvailable($teamId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_available_task] WHERE [id_team] = %i", $teamId);
        return $source;
    }

    /**
     * @return DibiDataSource
     */
    public function findSubmitAvailable($teamId) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_submit_available_task] WHERE [id_team] = %i", $teamId);

        // Find solved tasks
        $solved = Interlos::answers()
                ->findAllCorrect()
                ->where("[id_team] = %i", $teamId)
                ->fetchPairs("id_task", "id_task");
        // Remove solved tasks from the source
        if (!empty($solved)) {
            $source->where("[id_task] NOT IN %l", $solved);
        }
        return $source;
    }

    public function findAllStats() {
        return $this->getConnection()->dataSource("SELECT * FROM [tmp_task_stat]");
    }

    public function insert($name, $number, $serie, $type, $code) {
        $this->checkEmptiness($name, "name");
        $this->checkEmptiness($number, "number");
        $this->checkEmptiness($serie, "serie");
        $this->checkEmptiness($type, "type");
        $this->checkEmptiness($code, "code");
        $return = $this->getConnection()->insert("task", array(
                    "name" => $name,
                    "number" => $number,
                    "serie" => $serie,
                    "type" => $type,
                    "code" => $code,
                    "inserted" => $inserted
                ))->execute();
        $this->log(NULL, "task_inserted", "The task [$name] has been inserted.");
        return $return;
    }

    public function skip($team, $task) {
        $this->checkEmptiness($team, "team");
        $this->checkEmptiness($task, "task");

        // Check that skip is allowed for task
        $answers = Interlos::answers()->findAllCorrect($team)->where("[id_task] = %i", $task->id_task);
        if ($answers->count() > 0) {
            $this->log($team, "skip_tried", "The team tried to skip the task [$task->id_task].");
            throw new InvalidStateException("Skipping not allowed for the task [$task->id_task].", AnswersModel::ERROR_SKIP_OF_ANSWERED);
        }

        // Check that skip is allowed in period
        $skippableGroups = Interlos::groups()->findAllSkippable()->fetchPairs('id_group', 'id_group');
        if (!array_key_exists($task["id_group"], $skippableGroups)) {
            $this->log($team, "skip_tried", "The team tried to skip the task [$task->id_task].");
            throw new InvalidStateException("Skipping not allowed during this period.", AnswersModel::ERROR_SKIP_OF_PERIOD);
        }
        // Insert a skip record
        $return = $this->getConnection()->insert("task_state", array(
                    "id_team" => $team,
                    "id_task" => $task["id_task"],
                    "skipped" => 1))->execute();
        
        // Increase counter
        $sql = "INSERT INTO [group_state] ([id_group], [id_team], [task_counter])
                    VALUES(%i, %i, 0)
                ON DUPLICATE KEY UPDATE [task_counter] = [task_counter] + 1";
        $this->getConnection()->query($sql, $task->id_group, $team);
        
        // Log the action
        $this->log($team, "task_skipped", "The team successfuly skipped the task [$task->id_task].");
        return $return;
    }

    public function updateCounter($teamId) {
        // Initialize with zeroes
        $sql = "INSERT INTO [group_state] ([id_group], [id_team], [task_counter])
                    SELECT [id_group], [id_team], 0
                    FROM [view_group], [view_team]
                ON DUPLICATE KEY UPDATE [task_counter] = [task_counter]";
        $this->getConnection()->query($sql);


        // Update according to current period
        $sql = "UPDATE [group_state] AS gs
                SET task_counter = 
                    GREATEST(
                        IFNULL((SELECT COUNT([id_answer])
                            FROM [view_correct_answer] AS ca
                            LEFT JOIN [view_task] tsk USING ([id_task])
                            WHERE ca.[id_team] = gs.[id_team] AND tsk.[id_group] = gs.[id_group])
                        + (SELECT reserve_size FROM [period] AS p WHERE p.[id_group] = gs.[id_group] AND p.[begin] <= NOW() AND p.[end] > NOW()), 0),
                    gs.task_counter)";
        $this->getConnection()->query($sql);
    }

    public static function checkAnswer($task, $solution) {
        switch ($task->answer_type) {
            case self::TYPE_STR:
                return $solution == $task->answer_str;
                break;
            case self::TYPE_INT:
                return $solution == $task->answer_int;
                break;
            case self::TYPE_REAL:
                return abs($solution - $task->answer_real) <= $task->real_tolerance;
                break;
        }
    }

}
