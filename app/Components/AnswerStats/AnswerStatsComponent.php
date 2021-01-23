<?php

namespace FOL\Components\AnswerStats;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceTask;
use Nette\NotSupportedException;
use FOL\Components\BaseComponent;

class AnswerStatsComponent extends BaseComponent {

    private ServiceTask $serviceTask;
    private ServiceAnswer $serviceAnswer;

    private $taskId;

    public function injectPrimary(ServiceTask $serviceTask, ServiceAnswer $serviceAnswer): void {
        $this->serviceTask = $serviceTask;
        $this->serviceAnswer = $serviceAnswer;
    }

    public function render(?int $taskId = null): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerStats.latte');
        if (!is_numeric($taskId)) {
            throw new NotSupportedException();
        }
        $this->taskId = $taskId;
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    protected function beforeRender(): void {
        $answers = $this->serviceAnswer->findByTaskId($this->taskId);
        //$tasks = $this->tasksModel->findAll()->fetchAssoc('id_task');
        /** @var ModelTask $modelTask */
        $modelTask = $this->serviceTask->findByPrimary($this->taskId);

        //$taskNo = $task['id_group'].'_'.$task['number'];
        $tolerance = null;
        if ($modelTask->answer_type == 'int') {
            $correctValue = $modelTask->answer_int;
        } else {
            $correctValue = $modelTask->answer_real;
            $tolerance = $modelTask->real_tolerance;
        }

        $taskData = [];
        /** @var ModelAnswer $answer */
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
                'team' => $answer->getTeam()->name,
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

        $this->template->taskData = $taskData;
    }
}
