<?php

namespace App\FrontendModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use ChatListComponent;
use LoginFormComponent;
use Nette\Mail\IMailer;
use App\Model\Interlos;
use RecoverFormComponent;

class DefaultPresenter extends BasePresenter {

    protected TeamAuthenticator $authenticator;

    protected IMailer $mailer;

    public function injectSecondary(IMailer $mailer, TeamAuthenticator $authenticator): void {
        $this->mailer = $mailer;
        $this->authenticator = $authenticator;
    }

    public function actionLogout(): void {
        $this->getUser()->logout();
        $this->redirect("default");
    }

    public function renderChat(): void {
        $this->getComponent("chat")->setSource(Interlos::chat()->findAll($this->lang));
        $this->setPageTitle(_("Diskuse (česká verze)"));
    }

    public function renderDefault(): void {
        $this->setPagetitle(_("Mezinárodní soutež ve fyzice"));
        $this->template->year = Interlos::getCurrentYear();
        $this->changeViewByLang();
    }

    public function renderLastYears(): void {
        $this->setPagetitle(_("Minulé ročníky"));
        $this->changeViewByLang();
    }

    public function renderLogin(): void {
        $this->setPagetitle(_("Přihlásit se"));
    }

    public function renderRecover(): void {
        $this->setPageTitle(_("Obnova hesla"));
        if (!Interlos::isGameMigrated()) {
            $this->flashMessage(_("Změnu hesla proveďte editací vaší přihlášky."), "danger");
            $this->redirect("default");
        }
    }

    public function renderRules(): void {
        $this->setPagetitle(_("Pravidla"));
        $this->changeViewByLang();
    }

    public function renderFaq(): void {
        $this->setPagetitle(_("FAQ"));
        $this->changeViewByLang();
    }

    public function renderHowto(): void {
        $this->setPagetitle(_("Rychlý grafický návod ke hře"));
        $this->changeViewByLang();
    }

    public function renderTaskExamples(): void {
        $this->setPagetitle(_("Rozcvička"));
    }

    public function renderOtherEvents(): void {
        $this->setPagetitle(_("Další akce"));
        $this->changeViewByLang();
    }

    // ----- PROTECTED METHODS

    protected function createComponentChat(): ChatListComponent {
        return new ChatListComponent();
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->authenticator);
    }

    protected function createComponentRecover(): RecoverFormComponent {
        return new RecoverFormComponent($this->authenticator, $this->mailer, $this->getHttpRequest());
    }
}
