<?php

namespace App\Model;

use Nette;

class AnswersModel extends AbstractModel {

    const ERROR_TIME_LIMIT = 10;
    const ERROR_OUT_OF_PERIOD = 20;
    const ERROR_SKIP_OF_PERIOD = 30;
    const ERROR_SKIP_OF_ANSWERED = 31;

    public function find($id) {
        $this->checkEmptiness($id, "id");
        return $this->findAll()->where("[id_answer] = %i", $id)->fetch();
    }
    
    /**
     * @return DibiDataSource
     */
    public function findByTaskId($taskId) {
        return $this->findAll()->where("[id_task] = %i", $taskId);
    }

    /**
     * @return DibiDataSource
     */
    public function findAll($groupId = null) {
        if ($groupId === null) {
            return $this->getConnection()->dataSource("SELECT * FROM [view_answer]");
        } else {
            $source = $this->getConnection()->dataSource(
                    "SELECT [view_answer].*
                     FROM [view_answer]
                     RIGHT JOIN [view_task] ON [view_task].[id_task] = [view_answer].[id_task] AND [view_task].[id_group] = %i", $groupId);
            return $source;
        }
    }

    public function findAllCorrect($team = NULL) {
        $source = $this->getConnection()->dataSource("SELECT * FROM [view_correct_answer]");
        if (!empty($team)) {
            $source->where("[id_team] = %i", $team);
        }
        return $source;
    }

    public function insert($team, $task, $solution, $period, $correct) {
        $this->checkEmptiness($team, "team");
        $this->checkEmptiness($task, "task");
        $this->checkEmptiness($solution, "solution");
        // Correct answers of the team
        $correctAnswers = $this->findAllCorrect($team)
                ->fetchPairs("id_answer", "id_answer");
        // Last answer from same group has to be older than XX seconds
        $query = $this->findAll($task["id_group"])
                ->where("[id_team] = %i", $team)
                ->where("[inserted] > NOW() - INTERVAL %i SECOND", $period["time_penalty"]);
        if (!empty($correctAnswers)) {
            $query->where("[id_answer] NOT IN %l", $correctAnswers);
        }
        $row = $query->fetch();
        // Check it
        if ($row !== false) {
            $timestamp = strtotime($row['inserted']);
            $this->log($team, "solution_tried", "The team tried to insert the solution of task [$task->id_task] with code [$solution].");
            $remaining = $period["time_penalty"] - (time() - $timestamp);
            throw new Nette\InvalidStateException($remaining, self::ERROR_TIME_LIMIT);
        }
        $answer = array(
            "answer_str" => null,
            "answer_int" => null,
            "answer_real" => null,
        );
        switch ($task->answer_type) {
            case TasksModel::TYPE_STR:
                $answer["answer_str"] = $solution;
                break;
            case TasksModel::TYPE_INT:
                $answer["answer_int"] = $solution;
                break;
            case TasksModel::TYPE_REAL:
                $answer["answer_real"] = $solution;
                break;
        }
        // Insert a new answer
        $return = $this->getConnection()->insert("answer", array(
                    "id_team" => $team,
                    "id_task" => $task["id_task"],
                    "correct" => $correct,
                    "inserted" => new \DateTime()
                        ) + $answer)->execute();
        // Log the action
        $this->log($team, "solution_inserted", "The team successfuly inserted the solution of task [$task->id_task] with code [$solution].");
        return $return;
    }

}
