<?php

namespace FOL\Modules\PublicModule;

use Exception;
use FOL\Model\ORM\TeamsService;
use FOL\Components\Results\ResultsComponent;
use FOL\Components\ScoreList\ScoreListComponent;
use FOL\Components\TaskStats\TaskStatsComponent;
use Nette\Application\BadRequestException;

class StatsPresenter extends BasePresenter {

    const STATS_TAG = 'ctStats';

    protected TeamsService $teamsService;

    public function injectTeamsService(TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
    }

    /**
     * @return void
     * @throws BadRequestException
     * @throws \Dibi\Exception
     */
    protected function beforeRender(): void {
        parent::beforeRender();
        if (!$this->getCurrentYear()->isGameStarted()) {
            $this->error('Statistiky nejsou dostupné.');
        }
        $this->template->categories = $this->teamsService->getCategoryNames();
    }

    public function renderDefault($display = 'all'): void {
        $this->setPageTitle(_('Výsledky'));
        $this->check('results');
        $this->template->display = $display;
    }

    public function renderDetail(): void {
        $this->setPageTitle(_('Podrobné výsledky'));
        $this->check('scoreList');
    }

    public function renderTasks(): void {
        $this->setPageTitle(_('Statistika úkolů'));
        $this->check('taskStats');
    }

    protected function createComponentResults(): ResultsComponent {
        return new ResultsComponent($this->getContext());
    }

    protected function createComponentScoreList(): ScoreListComponent {
        return new ScoreListComponent($this->getContext());
    }

    protected function createComponentTaskStats(): TaskStatsComponent {
        return new TaskStatsComponent($this->getContext());
    }

    private function check($componentName): void {
        try {
            $this->getComponent($componentName);
            $this->template->available = true;
        } catch (Exception $e) {
            $this->flashMessage(_('Statistiky jsou momentálně nedostupné. Pravděpodobně dochází k přepočítávání.'), 'danger');
            $this->template->available = false;
        }
    }
}
