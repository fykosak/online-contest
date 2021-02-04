<?php

namespace FOL\Components\TaskStats;

use FOL\Model\ORM\TasksService;
use FOL\Components\BaseComponent;

final class TaskStatsComponent extends BaseComponent {

    public function injectTasksService(): void {
    }

    protected function beforeRender(): void {
      //  $this->template->tasks = $this->tasksService->findAllStats(); TODO
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'taskStats.latte');
        parent::render();
    }
}
