<?php

namespace App\FrontendModule\Presenters;

use AnswerStatsComponent;
use App\Model\AnswersModel;
use App\Model\Authentication\OrgAuthenticator;
use App\Model\ReportModel;
use App\Model\TasksModel;
use App\Model\TeamsModel;
use Exception;
use LoginFormComponent;
use ReportAddFormComponent;
use ResultsComponent;
use ScoreListComponent;
use TaskStatsComponent;

class OrgPresenter extends BasePresenter {

    const STATS_TAG = 'orgStats';

    protected OrgAuthenticator $authenticator;

    protected AnswersModel $answersModel;

    protected TasksModel $tasksModel;

    protected TeamsModel $teamsModel;

    protected ReportModel$reportModel;

    public function injectSecondary(ReportModel $reportModel, TeamsModel $teamsModel, TasksModel $tasksModel, AnswersModel $answersModel, OrgAuthenticator $authenticator) {
        $this->reportModel = $reportModel;
        $this->teamsModel = $teamsModel;
        $this->tasksModel = $tasksModel;
        $this->authenticator = $authenticator;
        $this->answersModel = $answersModel;
    }

    public function renderDefault(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Orgovský rozcestník"));
    }

    public function renderLogin(): void {
        $this->setPagetitle(_("Přihlásit se"));
    }

    public function renderAnswerStats($taskId = 1): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Statistiky odpovědí"));
        $this->template->taskId = $taskId;
        $this->template->tasks = $this->tasksModel->findAll()->fetchAll();
    }

    public function renderReport(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Správa reportů"));
    }

    public function renderStats(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Výsledky"));
        $this->check("results");
    }

    public function renderStatsDetail(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Podrobné výsledky"));
        $this->check("scoreList");
    }

    public function renderStatsTasks(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Statistika úkolů"));
        $this->check("taskStats");
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->authenticator);
    }

    protected function createComponentAnswerStats(): AnswerStatsComponent {
        return new AnswerStatsComponent($this->answersModel, $this->teamsModel, $this->tasksModel);
    }

    protected function createComponentReportAdd(): ReportAddFormComponent {
        return new ReportAddFormComponent($this->reportModel);
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
