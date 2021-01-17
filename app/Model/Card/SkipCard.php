<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\Card\Exceptions\NoTasksAvailableException;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FOL\Model\ORM\ScoreService;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class SkipCard extends Card {

    private const CONTAINER = 'tasks';

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
        foreach ($values[self::CONTAINER] as $taskId) {
            $task = $this->tasksService->findByPrimary($taskId);

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
     * @param Form $form
     * @param string $lang
     */
    public function decorateForm(Form $form, string $lang): void {
        $container = new Container();
        foreach ($this->getTasks() as $task) {
            $container->addCheckbox($task->id_task, $task['name_' . $lang]);
        }
        $form->addComponent($container, self::CONTAINER);
    }

    public function checkRequirements(): void {
        if (!count($this->getTasks())) {
            throw new NoTasksAvailableException();
        }
    }
}
