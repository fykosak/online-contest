<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Model\Card\Exceptions\NoTasksAvailableException;
use FOL\Model\Card\Exceptions\NoTasksWithHintAvailableException;
use FOL\Model\Card\Exceptions\TaskDoesNotHaveHintException;
use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Services\ServiceTaskHint;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Form;
use Nette\Utils\Html;

class HintCard extends Card {

    private ServiceTaskHint $serviceTaskHint;

    public function injectPrimary(ServiceTaskHint $serviceTaskHint): void {
        $this->serviceTaskHint = $serviceTaskHint;
    }

    protected function innerHandle(Logger $logger, array $values): void {
        $taskId = $values['task'];
        if (!isset($this->getTasks()[$taskId])) {
            throw new TaskNotAvailableException();
        }
        if (!$this->serviceTaskHint->getTaskHint($taskId)) {
            throw new TaskDoesNotHaveHintException();
        }
    }

    public function getType(): string {
        return 'hint';
    }

    public function getTitle(): string {
        return _('Hint');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    /**
     * @param Form $form
     * @param string $lang
     * @throws Exception
     */
    public function decorateForm(Form $form, string $lang): void {
        $items = [];
        foreach ($this->tasksService->findSubmitAvailable($this->team->id_team)->fetchAll() as $task) {
            if ($this->serviceTaskHint->getTaskHint($task->id_task)) {
                $items[$task->id_task] = $task['name_' . $lang];
            }
        }
        $form->addSelect('task', _('Task'), $items);
    }

    private function hasAnyTaskHint(): bool {
        foreach ($this->getTasks() as $task) {
            if ($this->serviceTaskHint->getTaskHint($task->id_taks)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws CardCannotBeUsedException
     */
    public function checkRequirements(): void {
        if (!$this->hasAnyTaskHint()) {
            throw new NoTasksWithHintAvailableException();
        }
    }
}
