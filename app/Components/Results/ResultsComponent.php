<?php

namespace FOL\Components\Results;

use Dibi\Exception;
use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseComponent;

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
     * @throws Exception
     */
    protected function beforeRender(): void {
        $this->template->display = $this->display;

        $this->template->teams = $this->teamsService->findAllWithScore();

        $competitors = $this->competitorsService->findAll();
        $teamCountries = [];
        foreach ($competitors as $competitor) {
            if (!array_key_exists($competitor->id_team, $teamCountries)) {
                $teamCountries[$competitor->id_team] = [];
            }
            $teamCountries[$competitor->id_team][] = $competitor->country_iso;
        }
        $this->template->teamCountries = $teamCountries;
        $this->template->categories = $this->teamsService->getCategoryNames();
        $this->template->bonus = $this->scoreService->findAllBonus();
        $this->template->penality = $this->scoreService->findAllPenality();

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
        $this->template->maxPoints = $maxPoints;
        $this->template->maxBonus = $maxBonus;
    }
}

