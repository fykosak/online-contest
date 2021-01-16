<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use Dibi\Row;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Form;
use Nette\Forms\Container;
use Nette\Utils\Html;

class OptionsCard extends Card {

    protected TasksService $tasksService;

    public function injectPrimary(TasksService $tasksService): void {
        $this->tasksService = $tasksService;
    }

    protected function innerHandle(Row $team, Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return 'options';
    }

    public function getTitle(): string {
        return _('4 options');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    /**
     * @param Form $form
     * @param Row $team
     * @param string $lang
     * @throws Exception
     */
    public function decorateForm(Form $form, Row $team, string $lang): void {
        $container = new Container();
        $items = [];
        foreach ($this->tasksService->findSubmitAvailable($team->id_team)->fetchAll() as $task) {
            $items[$task->id_task] = $task['name_' . $lang];
        }
        $form->addSelect('task', _('Task'), $items);
        $form->addComponent($container, 'tasks');
    }

    protected function isInnerAvailable(Row $team): bool {
        return (bool)$this->tasksService->findSubmitAvailable($team->id_team)->count(); // TODO hasTaskHint?
    }
}
