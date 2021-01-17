<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\Card\Exceptions\NoTasksAvailableException;
use FOL\Model\ORM\Models\ModelTask;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FOL\Model\ORM\ScoreService;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class SkipCard extends Card {

    protected ScoreService $scoreService;

    public function injectPrimary(ScoreService $scoreService): void {
        $this->scoreService = $scoreService;
    }

    /**
     * @param Logger $logger
     * @param array $values
     * @throws Exception
     */
    protected function innerHandle(Logger $logger, array $values): void {
        foreach ($values as $taskId) {
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($taskId);

            $this->tasksService->skip($this->team, $task);
            // TODO label
            $logger->log(new Message(sprintf(_('Úloha %s přeskočena.'), $taskId), 'success'));
            $this->tasksService->updateSingleCounter($this->team, $task);
            $this->scoreService->updateAfterSkip($this->team);
        }
    }

    public function getType(): string {
        return 'skip';
    }

    public function getTitle(): string {
        return _('Skip');
    }

    public function getDescription(): Html {
        return Html::el('p')->addText('Lorem ipsum.....');
    }

    /**
     * @param Container $container
     * @param string $lang
     * @throws Exception
     */
    public function decorateFormContainer(Container $container, string $lang): void {
        foreach ($this->getTasks() as $task) {
            $container->addCheckbox($task->id_task, $task['name_' . $lang]);
        }
    }

    /**
     * @throws Exception
     * @throws NoTasksAvailableException
     */
    public function checkRequirements(): void {
        if (!count($this->getTasks())) {
            throw new NoTasksAvailableException();
        }
    }
}
