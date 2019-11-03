<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;

class StatsPresenter extends BasePresenter {
    
    const STATS_TAG = 'ctStats';

    protected function beforeRender() {
        parent::beforeRender();
        if (!Interlos::isGameStarted()) {
            $this->error("Statistiky nejsou dostupné.");
        }
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

    public function renderDefault($display = 'all') {
        $this->setPageTitle(_("Výsledky"));
        $this->check("results");
        $this->template->display = $display;
    }

    public function renderDetail() {
        $this->setPageTitle(_("Podrobné výsledky"));
        $this->check("scoreList");
    }

    public function renderTasks() {
        $this->setPageTitle(_("Statistika úkolů"));
        $this->check("taskStats");
    }

    protected function createComponentResults($name) {
        return new \ResultsComponent($this, $name);
    }

    protected function createComponentScoreList($name) {
        return new \ScoreListComponent($this, $name);
    }

    protected function createComponentTaskStats($name) {
        return new \TaskStatsComponent($this, $name);
    }

    private function check($componentName) {
        try {
            $this->getComponent($componentName);
            $this->getTemplate()->available = TRUE;
        } catch (\Exception $e) {
            $this->flashMessage(_("Statistiky jsou momentálně nedostupné. Pravděpodobně dochází k přepočítávání."), "danger");
            $this->getTemplate()->available = FALSE;
        }
    }

}
