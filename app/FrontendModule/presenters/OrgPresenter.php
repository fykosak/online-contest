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
    
    protected function createComponentLogin($name) {
	return new \LoginFormComponent($this->authenticator, $this, $name);
    }
    
    protected function createComponentAnswerStats($name) {
	return new \AnswerStatsComponent($this->answersModel, $this->teamsModel, $this->tasksModel, $this, $name);
    }
    
    protected function createComponentReportAdd($name) {
        return new \ReportAddFormComponent($this->reportModel, $this, $name);
    }
}