<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\Card\Exceptions\NoTasksAvailableException;
use FOL\Model\Card\Exceptions\TaskNotAvailableException;
use FOL\Model\ORM\Models\ModelTask;
use Fykosak\Utils\Localization\GettextTranslator;
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

    protected function innerRenderUsage(string $lang, Html $mainContainer): void {
        $data = $this->deserializeData();
        $ulContainer = Html::el('ul');
        $mainContainer->addHtml($ulContainer);
        foreach ($data as $taskId => $skip) {
            if (!$skip) {
                continue;
            }
            $taskContainer = Html::el('li');
            /** @var ModelTask $task */
            $task = $this->serviceTask->findByPrimary($taskId);
            $taskContainer->addHtml(Html::el('strong')->addText($task->getGroup()->code_name)->addText($task->number));
            $taskContainer->addText(' ' . GettextTranslator::i18nHelper($task, 'name', $lang));
            $ulContainer->addHtml($taskContainer);
        }
    }

    /**
     * @throws Exception
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
