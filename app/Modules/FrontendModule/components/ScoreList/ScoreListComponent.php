<?php

use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;

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

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        parent::beforeRender();
        $this->getTemplate()->teams = $this->teamsService->findAllWithScore();
        $this->getTemplate()->score = $this->scoreService->findAllTasks();
        $this->getTemplate()->tasks = $this->tasksService
            ->findPossiblyAvailable();
        $this->getTemplate()->bonus = $this->scoreService->findAllBonus();
        $this->getTemplate()->penality = $this->scoreService->findAllPenality();
        $this->template->lang = $this->presenter->lang;
        $this->getTemplate()->categories = $this->teamsService->getCategoryNames();
    }

}
