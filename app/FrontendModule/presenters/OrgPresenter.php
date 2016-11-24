<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;

class OrgPresenter extends BasePresenter {
    
    /** @var \App\Model\Authentication\OrgAuthenticator @inject*/
    public $authenticator;
    
    /** @var \App\Model\AnswersModel @inject*/
    public $answersModel;
    
    /** @var \App\Model\TasksModel @inject*/
    public $tasksModel;
    
    /** @var \App\Model\TeamsModel @inject*/
    public $teamsModel;
    
    /** @var \App\Model\ReportModel @inject*/
    public $reportModel;
    
    const STATS_TAG = 'orgStats';
    
    public function renderDefault() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Orgovský rozcestník"));
    }
    
    public function renderLogin() {
	$this->setPagetitle(_("Přihlásit se"));
    }
    
    public function renderAnswerStats() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Statistiky odpovědí"));
    }
    
    public function renderReport() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Správa reportů"));
    }
    
    public function renderStats() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Výsledky"));
        $this->check("results");
    }

    public function renderStatsDetail() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Podrobné výsledky"));
        $this->check("scoreList");
    }

    public function renderStatsTasks() {
        if (!$this->user->isInRole('org')) {
            $this->redirect('login');
        }
        $this->setPageTitle(_("Statistika úkolů"));
        $this->check("taskStats");
    }
    
    protected function createComponentLogin($name) {
	return new \LoginFormComponent($this->authenticator, $this, $name);
    }
    
    protected function createComponentAnswerStats($name) {
	return new \AnswerStatsComponent($this->answersModel, $this->teamsModel, $this->tasksModel, $this, $name);
    }
    
    protected function createComponentReportAdd($name) {
        return new \ReportAddFormComponent($this->reportModel, $this, $name);
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