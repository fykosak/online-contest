<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use Dibi\Row;
use Fykosak\Utils\Logging\Logger;
use Fykosak\Utils\Logging\Message;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class SkipCard extends Card {

    protected TasksService $tasksService;
    protected ScoreService $scoreService;

    public function injectPrimary(TasksService $tasksService, ScoreService $scoreService): void {
        $this->tasksService = $tasksService;
        $this->scoreService = $scoreService;
    }

    /**
     * @param Row $team
     * @param Logger $logger
     * @param array $values
     * @throws Exception
     */
    protected function innerHandle(Row $team, Logger $logger, array $values): void {
        foreach ($values['tasks'] as $taskId) {
            $task = $this->tasksService->findByPrimary($taskId);

            $this->tasksService->skip($team, $task);
            //Environment::getCache()->clean(array(Cache::TAGS => array("problems/$team"))); not used
            // TODO label
            $logger->log(new Message(sprintf(_('Úloha %s přeskočena.'), $taskId), 'success'));
            $this->tasksService->updateSingleCounter($team, $task);
            $this->scoreService->updateAfterSkip($team);
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
     * @param Row $team
     * @param string $lang
     * @throws Exception
     */
    public function decorateForm(Form $form, Row $team, string $lang): void {
        $container = new Container();
        foreach ($this->tasksService->findSubmitAvailable($team->id_team)->fetchAll() as $task) {
            $container->addCheckbox($task->id_task, $task['name_' . $lang]);
        }
        $form->addComponent($container, 'tasks');
    }

    protected function isInnerAvailable(Row $team): bool {
        return (bool)$this->tasksService->findSubmitAvailable($team->id_team)->count();
    }
}
