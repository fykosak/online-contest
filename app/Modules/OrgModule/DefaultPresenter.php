<?php

namespace FOL\Modules\OrgModule;

use FOL\Model\ORM\Services\ServiceTask;
use FOL\Components\AnswerStats\AnswerStatsComponent;
use FOL\Components\Results\ResultsComponent;
use FOL\Components\ScoreList\ScoreListComponent;
use FOL\Components\TaskStats\TaskStatsComponent;

class DefaultPresenter extends BasePresenter {

    private ServiceTask $serviceTask;
    /** @persistent */
    public ?int $id = null;

    public function injectSecondary(ServiceTask $serviceTask): void {
        $this->serviceTask = $serviceTask;
    }

    public function renderDefault(): void {
        $this->setPageTitle(_('Orgovský rozcestník'));
    }

    public function renderAnswerStats(): void {
        $this->setPageTitle(_('Statistiky odpovědí'));
        $this->template->tasks = $this->serviceTask->getTable();
    }

    public function renderStats(): void {
        $this->setPageTitle(_('Výsledky'));
    }

    public function renderStatsDetail(): void {
        $this->setPageTitle(_('Podrobné výsledky'));
    }

    public function renderStatsTasks(): void {
        $this->setPageTitle(_('Statistika úkolů'));
    }

    protected function createComponentAnswerStats(): AnswerStatsComponent {
        return new AnswerStatsComponent($this->getContext(), $this->id);
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
