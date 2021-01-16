<?php

namespace FOL\Modules\FrontendModule\Components\TaskStats;

use Dibi\Exception;
use FOL\Model\ORM\TasksService;
use FOL\Modules\FrontendModule\Components\BaseComponent;

class TaskStatsComponent extends BaseComponent {

    private TasksService $tasksService;

    public function injectTasksService(TasksService $tasksService): void {
        $this->tasksService = $tasksService;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function beforeRender(): void {
        $this->getTemplate()->tasks = $this->tasksService->findAllStats();
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'taskStats.latte');
        parent::render();
    }
}
