<?php

namespace FOL\Components\AnswerStats;

use FOL\Model\ORM\Models\ModelAnswer;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTask;
use Nette\DI\Container;
use FOL\Components\BaseComponent;

class AnswerStatsComponent extends BaseComponent {

    private ServiceTask $serviceTask;
    private ?ModelTask $task;

    public function __construct(Container $container, ?int $taskId) {
        parent::__construct($container);
        $this->task = $taskId ? $this->serviceTask->findByPrimary($taskId) : null;
    }

    public function injectPrimary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'answerStats.latte');
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    protected function beforeRender(): void {
        if (!$this->task) {
            return;
        }
        $answers = $this->task->getAnswers();
        $tolerance = null;
        if ($this->task->answer_type == 'int') {
            $correctValue = $this->task->answer_int;
        } else {
            $correctValue = $this->task->answer_real;
            $tolerance = $this->task->real_tolerance;
        }

        $taskData = [];
        $taskData['correctValue'] = $correctValue;
        $taskData['tolerance'] = $tolerance;
        $taskData['answers'] = [];
        /** @var ModelAnswer $answer */
        foreach ($answers as $row) {
            $answer = ModelAnswer::createFromActiveRow($row);
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
        
        $taskData['correctValue'] = $correctValue;
        $taskData['tolerance'] = $tolerance;

        $count = count($taskData['answers']);

        $sum = 0;
        foreach ($taskData['answers'] as $answer) {
            $sum += $answer['value'];
        }
        $mu = $count ? ($sum / $count) : 0;

        $sum = 0;
        foreach ($taskData['answers'] as $answer) {
            $sum += ($answer['value'] - $mu) * ($answer['value'] - $mu);
        }

        $taskData['mu'] = $mu;

        $this->template->taskData = $taskData;
    }
}
