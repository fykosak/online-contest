<?php

namespace FOL\Model\ORM;

use DateTime;
use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelPeriod;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceLog;
use Nette\Database\Explorer;
use Nette\Database\Table\Selection;
use Nette\InvalidStateException;

class AnswersService extends AbstractService {

    const ERROR_TIME_LIMIT = 10;
    const ERROR_OUT_OF_PERIOD = 20;
    const ERROR_SKIP_OF_PERIOD = 30;
    const ERROR_SKIP_OF_ANSWERED = 31;

    private ServiceAnswer $serviceAnswer;

    public function __construct(ServiceAnswer $serviceAnswer, ServiceLog $serviceLog, Explorer $explorer) {
        parent::__construct($explorer, $serviceLog);
        $this->serviceAnswer = $serviceAnswer;
    }

    public function findAllCorrect(?int $teamId = null): Selection {
        $source = $this->explorer->table('view_correct_answer');
        if (!is_null($teamId)) {
            $source->where('id_team', $teamId);
        }
        return $source;
    }

    /**
     * @param ModelTeam $team
     * @param $task
     * @param $solution
     * @param $period
     * @param $correct
     * @param bool $isDoublePoints
     * @return int
     * TODO double points
     */
    public function insert(ModelTeam $team, ModelTask $task, $solution, ModelPeriod $period, bool $correct, bool $isDoublePoints): int {
        $this->explorer->beginTransaction();
        // Correct answers of the team
        $correctAnswers = $this->findAllCorrect($team->id_team)
            ->fetchPairs('id_answer', 'id_answer');
        // Last answer from same group has to be older than XX seconds
        $query = $this->serviceAnswer->getTable()->where('task.id_group', $task->id_group)
            ->where('id_team', $team->id_team)
            ->where('inserted > NOW() - INTERVAL ? SECOND', $period->time_penalty);
        if (!empty($correctAnswers)) {
            $query->where('id_answer NOT IN ?', $correctAnswers);
        }
        /** @var ModelAnswer $row */
        $row = $query->fetch();
        // Check it
        if ($row) {
            $timestamp = strtotime($row->inserted);
            $this->log($team->id_team, 'solution_tried', 'The team tried to insert the solution of task [$task->id_task] with code [$solution].');
            $remaining = $period->time_penalty - (time() - $timestamp);
            $this->explorer->commit();
            throw new InvalidStateException($remaining, self::ERROR_TIME_LIMIT);
        }
        $answer = [
            'answer_str' => null,
            'answer_int' => null,
            'answer_real' => null,
        ];
        switch ($task->answer_type) {
            case TasksService::TYPE_STR:
                $answer['answer_str'] = $solution;
                break;
            case TasksService::TYPE_INT:
                $answer['answer_int'] = $solution;
                break;
            case TasksService::TYPE_REAL:
                $answer['answer_real'] = $solution;
                break;
        }
        // Insert a new answer
        $modelAnswer = $this->serviceAnswer->createNewModel([
                'id_team' => $team->id_team,
                'id_task' => $task->id_task,
                'correct' => $correct,
                'inserted' => new DateTime(),
            ] + $answer);
        // Log the action
        $this->log($team->id_team, 'solution_inserted', 'The team successfully inserted the solution of task [$task->id_task] with code [$solution].');
        $this->explorer->commit();
        return $modelAnswer->getPrimary();
    }
}
