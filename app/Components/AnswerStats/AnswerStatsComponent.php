<?php

namespace FOL\Components\AnswerStats;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceAnswer;
use FOL\Model\ORM\Services\ServiceTask;
use Nette\DI\Container;
use FOL\Components\BaseComponent;

class AnswerStatsComponent extends BaseComponent {

    private ServiceTask $serviceTask;
    private ServiceAnswer $serviceAnswer;
    private ?ModelTask $task;

    public function __construct(Container $container, ?int $taskId) {
        parent::__construct($container);
        $this->task = $taskId ? $this->serviceTask->findByPrimary($taskId) : null;
    }

    public function injectPrimary(ServiceTask $serviceTask, ServiceAnswer $serviceAnswer): void {
        $this->serviceTask = $serviceTask;
        $this->serviceAnswer = $serviceAnswer;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerStats.latte');
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    protected function beforeRender(): void {
        $answers = $this->serviceAnswer->findByTaskId($this->task->id_task);
        $tolerance = null;
        if ($this->task->answer_type == 'int') {
            $correctValue = $this->task->answer_int;
        } else {
            $correctValue = $this->task->answer_real;
            $tolerance = $this->task->real_tolerance;
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
