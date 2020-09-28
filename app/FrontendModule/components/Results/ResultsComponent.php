<?php

use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;

class ResultsComponent extends BaseComponent {
    private $display;

    protected TasksService $tasksService;
    protected TeamsService $teamsService;
    protected ScoreService $scoreService;
    protected CompetitorsService $competitorsService;

    public function injectPrimary(
        TasksService $tasksService,
        TeamsService $teamsService,
        ScoreService $scoreService,
        CompetitorsService $competitorsService
    ): void {
        $this->tasksService = $tasksService;
        $this->teamsService = $teamsService;
        $this->scoreService = $scoreService;
        $this->competitorsService = $competitorsService;
    }

    public function render($display = 'all'): void {
        $this->display = $display;
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
        parent::render();
    }

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        $this->getTemplate()->display = $this->display;

        $this->getTemplate()->teams = $this->teamsService->findAllWithScore();

        $competitors = $this->competitorsService->findAll();
        $teamCountries = [];
        foreach ($competitors as $competitor) {
            if (!array_key_exists($competitor->id_team, $teamCountries)) {
                $teamCountries[$competitor->id_team] = [];
            }
            $teamCountries[$competitor->id_team][] = $competitor->country_iso;
        }
        $this->getTemplate()->teamCountries = $teamCountries;
        $this->getTemplate()->categories = $this->teamsService->getCategoryNames();
        $this->getTemplate()->bonus = $this->scoreService->findAllBonus();
        $this->getTemplate()->penality = $this->scoreService->findAllPenality();

        $tasks = $this->tasksService->findAll();
        $maxBonus = 0;
        $maxPoints = 0;
        foreach ($tasks as $task) {
            $hurry = ($task->id_group == 1) ? false : true; //dle SQL id_group=2,3,4
            $maxPoints += $task->points;
            if ($hurry) {
                $maxBonus += $task->points;
            }
        }
        $maxPoints += $maxBonus;
        $this->getTemplate()->maxPoints = $maxPoints;
        $this->getTemplate()->maxBonus = $maxBonus;
    }
}

