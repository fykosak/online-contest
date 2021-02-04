<?php

namespace FOL\Model\Card;

use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Model\Card\Exceptions\NoTasksWithHintAvailableException;
use FOL\Model\Card\Exceptions\TaskDoesNotHaveHintException;
use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\Services\ServiceTaskHint;
use Fykosak\Utils\Logging\Logger;
use Nette\Database\Table\ActiveRow;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class HintCard extends SingleFormCard {

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
        if (!$this->serviceTaskHint->findByPrimary($taskId)) {
            throw new TaskDoesNotHaveHintException();
        }
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_HINT;
    }

    public function getTitle(): string {
        return _('Hint (only some problems)');
    }

    public function getDescription(): Html {
        return Html::el('span')->addText('The team can read a hint to the solution (only some of the problems have hints).');
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        /** @var ModelTask|ActiveRow $task */
        foreach ($this->team->getSubmitAvailableTasks()->fetchAll() as $task) {
            if ($this->serviceTaskHint->findByPrimary($task->id_task)) {
                /** @var ModelTask $t */
                $t = $this->serviceTask->findByPrimary($task->id_task);
                $items[$task->id_task] = $t->getLabel($lang);
            }
        }
        $container->addSelect('task', _('Task'), $items);
    }

    private function hasAnyTaskHint(): bool {
        foreach ($this->getTasks() as $task) {
            if ($this->serviceTaskHint->findByPrimary($task->id_task)) {
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
