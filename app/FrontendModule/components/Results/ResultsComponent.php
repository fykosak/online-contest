<?php

use App\Model\Interlos;

class ResultsComponent extends BaseComponent
{
    private $display;

    public function render($display = 'all') {
        $this->display = $display;
        parent::render();
    }

    protected function beforeRender() {
        $this->getTemplate()->display = $this->display;

        $this->getTemplate()->teams = Interlos::teams()
            ->findAllWithScore();

        $competitors = Interlos::competitors()->findAll();
        $teamCountries = [];
        foreach ($competitors as $competitor) {
            if (!array_key_exists($competitor->id_team, $teamCountries)) {
                $teamCountries[$competitor->id_team] = [];
            }
            $teamCountries[$competitor->id_team][] = $competitor->country_iso;
        }
        $this->getTemplate()->teamCountries = $teamCountries;

        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();

        $this->getTemplate()->bonus = Interlos::score()
            ->findAllBonus();

        $this->getTemplate()->penality = Interlos::score()
            ->findAllPenality();

        $tasks = Interlos::tasks()->findAll();
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

