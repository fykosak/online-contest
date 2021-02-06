<?php

namespace FOL\Model\Card;

use FOL\Model\Card\Exceptions\NoTasksAvailableException;
use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelTask;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FOL\Model\ORM\ScoreService;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class SkipCard extends SingleFormCard {

    protected ScoreService $scoreService;

    public function injectPrimary(ScoreService $scoreService): void {
        $this->scoreService = $scoreService;
    }

    /**
     * @param Logger $logger
     * @param array $values
     * @throws TaskNotAvailableException
     */
    protected function innerHandle(Logger $logger, array $values): void {
        foreach ($values as $taskId => $skip) {
            if (!$skip) {
                continue;
            }
            if (!isset($this->getTasks()[$taskId])) {
                throw new TaskNotAvailableException();
            }
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($taskId);

            $this->tasksService->skip($this->team, $task);
            $logger->log(new Message(sprintf(_('Úloha %s přeskočena.'), $taskId), 'success'));
            $this->tasksService->updateSingleCounter($this->team, $task->getGroup());
        }
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_SKIP;
    }

    public function getTitle(): string {
        return _('Skip the problems');
    }

    public function getDescription(): Html {
        return Html::el('p')->addText('The team can skip an arbitrary number of problems they are currently solving.');
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        foreach ($this->getTasks() as $task) {
            $container->addCheckbox($task->id_task, ModelTask::createFromActiveRow($task)->getLabel($lang));
        }
    }

    /**
     * @throws NoTasksAvailableException
     * @throws Exceptions\CardCannotBeUsedException
     */
    public function checkRequirements(): void {
        parent::checkRequirements();
        if (!count($this->getTasks())) {
            throw new NoTasksAvailableException();
        }
    }
}
