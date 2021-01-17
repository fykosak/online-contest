<?php

namespace FOL\Modules\OrgModule;

use FOL\Model\Authentication\OrgAuthenticator;
use Exception;
use FOL\Model\ORM\TasksService;
use FOL\Components\AnswerStats\AnswerStatsComponent;
use FOL\Components\LoginForm\LoginFormComponent;
use FOL\Components\Results\ResultsComponent;
use FOL\Components\ScoreList\ScoreListComponent;
use FOL\Components\TaskStats\TaskStatsComponent;

class OrgPresenter extends BasePresenter {

    const STATS_TAG = 'orgStats';

    protected OrgAuthenticator $authenticator;
    protected TasksService $tasksModel;

    public function injectSecondary(TasksService $tasksModel, OrgAuthenticator $authenticator) {
        $this->tasksModel = $tasksModel;
        $this->authenticator = $authenticator;
    }

    protected function startUp(): void {
        if (!$this->user->isInRole('org') && $this->getAction() !== 'login') {
            $this->redirect('login');
        }
        parent::startUp();
    }

    public function renderDefault(): void {
        $this->setPageTitle(_('Orgovský rozcestník'));
    }

    public function renderLogin(): void {
        $this->setPagetitle(_('Přihlásit se'));
    }

    /**
     * @param int $taskId
     * @return void
     * @throws \Dibi\Exception
     */
    public function renderAnswerStats($taskId = 1): void {
        $this->setPageTitle(_('Statistiky odpovědí'));
        $this->template->taskId = $taskId;
        $this->template->tasks = $this->tasksModel->findAll()->fetchAll();
    }

    public function renderStats(): void {
        $this->setPageTitle(_('Výsledky'));
        $this->check('results');
    }

    public function renderStatsDetail(): void {
        $this->setPageTitle(_('Podrobné výsledky'));
        $this->check('scoreList');
    }

    public function renderStatsTasks(): void {
        $this->setPageTitle(_('Statistika úkolů'));
        $this->check('taskStats');
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator, ':Org:Org:default');
    }

    protected function createComponentAnswerStats(): AnswerStatsComponent {
        return new AnswerStatsComponent($this->getContext());
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
