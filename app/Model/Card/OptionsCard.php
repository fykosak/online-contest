<?php

namespace FOL\Model\Card;

use Dibi\Exception;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Forms\Container;
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

    /**
     * @param Container $container
     * @param string $lang
     * @throws Exception
     */
    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->getTasks() as $task) {
            $items[$task->id_task] = $task['name_' . $lang];
        }
        $container->addSelect('task', _('Task'), $items);
    }

    public function checkRequirements(): void {
        // TODO: Implement isInnerAvailable() method.
    }
}
