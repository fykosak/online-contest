<?php

namespace FOL\Modules\OrgModule\Presenters;

use AnswerStatsComponent;
use App\Model\Authentication\OrgAuthenticator;
use Exception;
use FOL\Model\ORM\AnswersService;
use FOL\Model\ORM\ReportService;
use FOL\Model\ORM\TasksService;
use FOL\Model\ORM\TeamsService;
use LoginFormComponent;
use ReportAddFormComponent;
use ResultsComponent;
use ScoreListComponent;
use TaskStatsComponent;

class OrgPresenter extends BasePresenter {

    const STATS_TAG = 'orgStats';

    protected OrgAuthenticator $authenticator;
    protected AnswersService $answersModel;
    protected TasksService $tasksModel;
    protected TeamsService $teamsModel;
    protected ReportService $reportModel;

    public function injectSecondary(ReportService $reportModel, TeamsService $teamsModel, TasksService $tasksModel, AnswersService $answersModel, OrgAuthenticator $authenticator) {
        $this->reportModel = $reportModel;
        $this->teamsModel = $teamsModel;
        $this->tasksModel = $tasksModel;
        $this->authenticator = $authenticator;
        $this->answersModel = $answersModel;
    }

    protected function startUp(): void {
        if (!$this->user->isInRole('org') && $this->getAction() !== 'login') {
            $this->redirect('login');
        }
        parent::startUp();
    }

    public function renderDefault(): void {
        $this->setPageTitle(_("Orgovský rozcestník"));
    }

    public function renderLogin(): void {
        $this->setPagetitle(_("Přihlásit se"));
    }

    /**
     * @param int $taskId
     * @return void
     * @throws \Dibi\Exception
     */
    public function renderAnswerStats($taskId = 1): void {
        $this->setPageTitle(_("Statistiky odpovědí"));
        $this->template->taskId = $taskId;
        $this->template->tasks = $this->tasksModel->findAll()->fetchAll();
    }

    public function renderReport(): void {
        $this->setPageTitle(_("Správa reportů"));
    }

    public function renderStats(): void {
        $this->setPageTitle(_("Výsledky"));
        $this->check("results");
    }

    public function renderStatsDetail(): void {
        $this->setPageTitle(_("Podrobné výsledky"));
        $this->check("scoreList");
    }

    public function renderStatsTasks(): void {
        $this->setPageTitle(_("Statistika úkolů"));
        $this->check("taskStats");
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(), $this->authenticator, ':Org:Org:default');
    }

    protected function createComponentAnswerStats(): AnswerStatsComponent {
        return new AnswerStatsComponent($this->getContext());
    }

    protected function createComponentReportAdd(): ReportAddFormComponent {
        return new ReportAddFormComponent($this->getContext());
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
            $this->getTemplate()->available = true;
        } catch (Exception $e) {
            $this->flashMessage(_("Statistiky jsou momentálně nedostupné. Pravděpodobně dochází k přepočítávání."), "danger");
            $this->getTemplate()->available = false;
        }
    }
}
