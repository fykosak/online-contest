<?php

use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;
use Nette\NotSupportedException;

class AnswerStatsComponent extends BaseComponent {

    private AnswersService $answersModel;

    private TasksService $tasksModel;

    private TeamsService $teamsModel;

    private $taskId;

    public function injectPrimary(AnswersService $answersModel, TeamsService $teamsModel, TasksService $tasksModel): void {
        $this->answersModel = $answersModel;
        $this->teamsModel = $teamsModel;
        $this->tasksModel = $tasksModel;
    }

    /**
     * @param null $taskId
     * @return void
     * @throws \Dibi\Exception
     */
    public function render($taskId = null): void {
        if (!is_numeric($taskId)) {
            throw new NotSupportedException();
        }
        $this->taskId = $taskId;
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        $answers = $this->answersModel->findByTaskId($this->taskId)->fetchAll();
        //$tasks = $this->tasksModel->findAll()->fetchAssoc('id_task');
        $task = $this->tasksModel->find($this->taskId);
        $teams = $this->teamsModel->findAll()->fetchAssoc('id_team');


        //$taskNo = $task['id_group'].'_'.$task['number'];

        if ($task['answer_type'] == 'int') {
            $correctValue = $task['answer_int'];
        } else {
            $correctValue = $task['answer_real'];
            $tolerance = $task['real_tolerance'];
        }

        $taskData = [];

        foreach ($answers as $answer) {

            if (isset($answer->answer_int)) {
                $trueValue = $answer->answer_int;
                $value = $trueValue - $correctValue;
            } else {
                $trueValue = $answer->answer_real;
                $value = (($correctValue - $trueValue > 0) ? 1 : -1) * log(1.0 + abs($trueValue - $correctValue) / $tolerance, 2.0);
            }

            $taskData['answers'][] = [
                'value' => $value,
                'trueValue' => $trueValue,
                'team' => $teams[$answer->id_team]['name'],
                'inserted' => $answer->inserted->getTimestamp(),
            ];
        }

        $count = count($taskData['answers']);

        $sum = 0;
        foreach ($taskData['answers'] as $answer) {
            $sum += $answer['value'];
        }
        $mu = $sum / $count;

        $sum = 0;
        foreach ($taskData['answers'] as $answer) {
            $sum += ($answer['value'] - $mu) * ($answer['value'] - $mu);
        }
        $sigma = sqrt($sum / ($count - 1));

        $taskData['mu'] = $mu;
        $taskData['sigma'] = $sigma;

        $this->getTemplate()->taskData = $taskData;
    }
}
