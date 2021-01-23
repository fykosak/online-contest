<?php

namespace FOL\Components\ScoreList;

use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseComponent;

class ScoreListComponent extends BaseComponent {

    protected TasksService $tasksService;
    protected TeamsService $teamsService;
    protected ScoreService $scoreService;

    public function injectPrimary(
        TasksService $tasksService,
        TeamsService $teamsService,
        ScoreService $scoreService
    ): void {
        $this->tasksService = $tasksService;
        $this->teamsService = $teamsService;
        $this->scoreService = $scoreService;
    }

    protected function beforeRender(): void {
        parent::beforeRender();
        $this->template->teams = $this->teamsService->findAllWithScore();
        $this->template->score = $this->scoreService->findAllTasks();
        $this->template->tasks = $this->tasksService
            ->findPossiblyAvailable();
        $this->template->bonus = $this->scoreService->findAllBonus();
        $this->template->penality = $this->scoreService->findAllPenality();
        $this->template->lang = $this->presenter->lang;
        $this->template->categories = $this->teamsService->getCategoryNames();
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'scoreList.latte');
        parent::render();
    }
}
