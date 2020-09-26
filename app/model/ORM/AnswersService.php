<?php

namespace FOL\Model\ORM;

use DateTime;
use Dibi\DataSource;
use Dibi\Exception;
use Dibi\Result;
use Dibi\Row;
use Nette\InvalidStateException;

class AnswersService extends AbstractService {

    const ERROR_TIME_LIMIT = 10;
    const ERROR_OUT_OF_PERIOD = 20;
    const ERROR_SKIP_OF_PERIOD = 30;
    const ERROR_SKIP_OF_ANSWERED = 31;

    /**
     * @param $id
     * @return Row|false
     * @throws Exception
     */
    public function find($id) {
        return $this->findAll()->where("[id_answer] = %i", $id)->fetch();
    }

    /**
     * @param $taskId
     * @return DataSource
     * @throws Exception
     */
    public function findByTaskId($taskId): DataSource {
        return $this->findAll()->where("[id_task] = %i", $taskId);
    }

    /**
     * @param null $groupId
     * @return DataSource
     * @throws Exception
     */
    public function findAll($groupId = null): DataSource {
        if ($groupId === null) {
            return $this->getDibiConnection()->dataSource("SELECT * FROM [view_answer]");
        } else {
            return $this->getDibiConnection()->dataSource(
                "SELECT [view_answer].*
                     FROM [view_answer]
                     RIGHT JOIN [view_task] ON [view_task].[id_task] = [view_answer].[id_task] AND [view_task].[id_group] = %i", $groupId);
        }
    }

    /**
     * @param null $team
     * @return DataSource
     * @throws Exception
     */
    public function findAllCorrect($team = null): DataSource {
        $source = $this->getDibiConnection()->dataSource("SELECT * FROM [view_correct_answer]");
        if (!empty($team)) {
            $source->where("[id_team] = %i", $team);
        }
        return $source;
    }

    /**
     * @param $team
     * @param $task
     * @param $solution
     * @param $period
     * @param $correct
     * @return Result|int
     * @throws Exception
     */
    public function insert($team, $task, $solution, $period, $correct) {

        $this->getDibiConnection()->begin();
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
            $this->getDibiConnection()->commit();
            throw new InvalidStateException($remaining, self::ERROR_TIME_LIMIT);
        }
        $answer = [
            "answer_str" => null,
            "answer_int" => null,
            "answer_real" => null,
        ];
        switch ($task->answer_type) {
            case TasksService::TYPE_STR:
                $answer["answer_str"] = $solution;
                break;
            case TasksService::TYPE_INT:
                $answer["answer_int"] = $solution;
                break;
            case TasksService::TYPE_REAL:
                $answer["answer_real"] = $solution;
                break;
        }
        // Insert a new answer
        $return = $this->getDibiConnection()->insert("answer", [
                "id_team" => $team,
                "id_task" => $task["id_task"],
                "correct" => $correct,
                "inserted" => new DateTime(),
            ] + $answer)->execute();
        // Log the action
        $this->log($team, "solution_inserted", "The team successfuly inserted the solution of task [$task->id_task] with code [$solution].");
        $this->getDibiConnection()->commit();
        return $return;
    }

    protected function getTableName(): string {
        return 'answer';
    }
}
