<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use Dibi\Row;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\UI\Form;
use Nette\Utils\Html;

class OptionsCard extends Card {

    protected TasksService $tasksService;

    public function injectPrimary(TasksService $tasksService): void {
        $this->tasksService = $tasksService;
    }

    protected function innerHandle(Logger $logger, array $values): void {
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

    public function decorateForm(Form $form, string $lang): void {
        $items = [];
        foreach ($this->getTasks() as $task) {
            $items[$task->id_task] = $task['name_' . $lang];
        }
        $form->addSelect('task', _('Task'), $items);
    }

    public function checkRequirements(): void {
        // TODO: Implement isInnerAvailable() method.
    }
}
