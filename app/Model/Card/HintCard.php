<?php

namespace FOL\Model\Card;

use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Model\Card\Exceptions\NoTasksWithHintAvailableException;
use FOL\Model\Card\Exceptions\TaskDoesNotHaveHintException;
use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTaskHint;
use Fykosak\Utils\Logging\Logger;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;
use Nette\Utils\Html;

class HintCard extends Card {

    private ServiceTaskHint $serviceTaskHint;

    public function injectPrimary(ServiceTaskHint $serviceTaskHint): void {
        $this->serviceTaskHint = $serviceTaskHint;
    }

    /**
     * @param Logger $logger
     * @param array $values
     * @throws TaskDoesNotHaveHintException
     * @throws TaskNotAvailableException
     */
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

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        /** @var ModelTask|ActiveRow $task */
        foreach ($this->tasksService->findSubmitAvailable($this->team)->fetchAll() as $task) {
            if ($this->serviceTaskHint->getTaskHint($task->id_task)) {
                $items[$task->id_task] = $task['name_' . $lang];
            }
        }
        $container->addSelect('task', _('Task'), $items);
    }

    private function hasAnyTaskHint(): bool {
        foreach ($this->getTasks() as $task) {
            if ($this->serviceTaskHint->getTaskHint($task->id_task)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @throws NoTasksWithHintAvailableException
     * @throws CardCannotBeUsedException
     */
    public function checkRequirements(): void {
        parent::checkRequirements();
        if (!$this->hasAnyTaskHint()) {
            throw new NoTasksWithHintAvailableException();
        }
    }
}
