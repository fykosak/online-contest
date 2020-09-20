<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;
use Exception;
use ResultsComponent;
use ScoreListComponent;
use TaskStatsComponent;

class StatsPresenter extends BasePresenter {

    const STATS_TAG = 'ctStats';

    protected function beforeRender(): void {
        parent::beforeRender();
        if (!Interlos::isGameStarted()) {
            $this->error("Statistiky nejsou dostupné.");
        }
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

    public function renderDefault($display = 'all'): void {
        $this->setPageTitle(_("Výsledky"));
        $this->check("results");
        $this->template->display = $display;
    }

    public function renderDetail(): void {
        $this->setPageTitle(_("Podrobné výsledky"));
        $this->check("scoreList");
    }

    public function renderTasks(): void {
        $this->setPageTitle(_("Statistika úkolů"));
        $this->check("taskStats");
    }

    protected function createComponentResults(): ResultsComponent {
        return new ResultsComponent();
    }

    protected function createComponentScoreList(): ScoreListComponent {
        return new ScoreListComponent();
    }

    protected function createComponentTaskStats(): TaskStatsComponent {
        return new TaskStatsComponent();
    }

    private function check($componentName): void {
        try {
            $this->getComponent($componentName);
            $this->getTemplate()->available = true;
        } catch (Exception $e) {
            $this->flashMessage(_("Statistiky jsou momentálně nedostupné. Pravděpodobně dochází k přepočítávání."), "danger");
            $this->getTemplate()->available = false;
        }
    }
}
