<?php

namespace FOL\Modules\PublicModule;

use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\Results\ResultsComponent;
use FOL\Components\ScoreList\ScoreListComponent;
use FOL\Components\TaskStats\TaskStatsComponent;
use Nette\Application\BadRequestException;

class StatsPresenter extends BasePresenter {

    const STATS_TAG = 'ctStats';

    protected ServiceTeam $teamsService;

    public function injectTeamsService(ServiceTeam $teamsService): void {
        $this->teamsService = $teamsService;
    }

    /**
     * @return void
     * @throws BadRequestException
     */
    protected function beforeRender(): void {
        parent::beforeRender();
        if (!$this->getCurrentYear()->isGameStarted()) {
            $this->error('Statistiky nejsou dostupné.');
        }
        $this->template->categories = $this->teamsService->getCategoryNames();
    }

    /**
     * @param string $display
     */
    public function renderDefault($display = 'all'): void {
        $this->setPageTitle(_('Výsledky'));
        $this->template->display = $display;
    }

    public function renderDetail(): void {
        $this->setPageTitle(_('Podrobné výsledky'));
    }

    public function renderTasks(): void {
        $this->setPageTitle(_('Statistika úkolů'));
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
}
