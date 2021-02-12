<?php

namespace FOL\Model\ORM;

use DateTime;
use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceLog;
use Nette\Database\Explorer;
use Nette\InvalidStateException;

final class AnswersService extends AbstractService {

    const ERROR_TIME_LIMIT = 10;
    const ERROR_OUT_OF_PERIOD = 20;
    const ERROR_SKIP_OF_PERIOD = 30;
    const ERROR_SKIP_OF_ANSWERED = 31;

    private ServiceAnswer $serviceAnswer;

    public function __construct(ServiceAnswer $serviceAnswer, ServiceLog $serviceLog, Explorer $explorer) {
        parent::__construct($explorer, $serviceLog);
        $this->serviceAnswer = $serviceAnswer;
    }

    /**
     * @param ModelTeam $team
     * @param ModelTask $task
     * @param int|float|string $solution
     * @param bool $correct
     * @param bool $isDoublePoints
     * @return ModelAnswer
     */
    public function insert(ModelTeam $team, ModelTask $task, $solution, bool $correct, bool $isDoublePoints): ModelAnswer {
        $period = $task->getGroup()->getActivePeriod();
        if (!$period) {
            $this->log($team->id_team, 'solution_tried', sprintf('The team tried to insert the solution of task [%i] with solution [%s].', $task->id_task, $solution));
            throw new InvalidStateException('There is no active submit period.', AnswersService::ERROR_OUT_OF_PERIOD);
        }

        // Correct answers of the team
        $correctAnswers = $team->getCorrect()->fetchPairs('id_answer', 'id_answer');
        // Last answer from same group has to be older than XX seconds
        $query = $this->serviceAnswer->getTable()
            ->where('task.id_group', $task->id_group)
            ->where('id_team', $team->id_team)
            ->where('answer.inserted > NOW() - INTERVAL ? SECOND', $period->time_penalty);
        if (!empty($correctAnswers)) {
            $query->where('id_answer NOT IN ?', $correctAnswers);
        }
        /** @var ModelAnswer $row */
        $row = $query->fetch();
        // Check it
        if ($row) {
            $timestamp = strtotime($row->inserted);
            $this->log($team->id_team, 'solution_tried', sprintf('The team tried to insert the solution of task [%i] with code [%s]', $task->id_task, $solution));
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
            case ModelTask::TYPE_STR:
                $answer['answer_str'] = $solution;
                break;
            case  ModelTask::TYPE_INT:
                $answer['answer_int'] = $solution;
                break;
            case ModelTask::TYPE_REAL:
                $answer['answer_real'] = $solution;
                break;
        }
        // Insert a new answer
        /** @var ModelAnswer $modelAnswer */
        $modelAnswer = $this->serviceAnswer->createNewModel([
                'id_team' => $team->id_team,
                'id_task' => $task->id_task,
                'correct' => $correct,
                'inserted' => new DateTime(),
                'double_points' => $isDoublePoints,
            ] + $answer);
        // Log the action
        $this->log($team->id_team, 'solution_inserted', 'The team successfully inserted the solution of task [$task->id_task] with code [$solution].');
        return $modelAnswer;
    }
}
