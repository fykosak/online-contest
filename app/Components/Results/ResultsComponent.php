<?php

namespace FOL\Components\Results;

use FOL\Model\ORM\Models\ModelCompetitor;
use FOL\Model\ORM\Models\ModelTask;
use FOL\Model\ORM\ScoreService;
use FOL\Model\ORM\Services\ServiceCompetitor;
use FOL\Model\ORM\Services\ServiceTask;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseComponent;

class ResultsComponent extends BaseComponent {

    private $display;

    protected TasksService $tasksService;
    protected TeamsService $teamsService;
    protected ScoreService $scoreService;
    protected ServiceCompetitor $serviceCompetitors;
    private ServiceTask $serviceTask;

    public function injectPrimary(
        TasksService $tasksService,
        TeamsService $teamsService,
        ScoreService $scoreService,
        ServiceCompetitor $serviceCompetitors,
        ServiceTask $serviceTask
    ): void {
        $this->tasksService = $tasksService;
        $this->teamsService = $teamsService;
        $this->scoreService = $scoreService;
        $this->serviceCompetitors = $serviceCompetitors;
        $this->serviceTask = $serviceTask;
    }

    /**
     * @param string $display
     */
    public function render($display = 'all'): void {
        $this->display = $display;
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'results.latte');
        parent::render();
    }

    protected function beforeRender(): void {
        $this->template->display = $this->display;

        $this->template->teams = $this->teamsService->findAllWithScore();

        $competitors = $this->serviceCompetitors->getTable();
        $teamCountries = [];
        /** @var ModelCompetitor $competitor */
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

        $maxBonus = 0;
        $maxPoints = 0;
        /** @var ModelTask $task */
        foreach ($this->serviceTask->getTable() as $task) {
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

