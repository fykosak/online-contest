<?php

namespace App\FrontendModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use ChatListComponent;
use Dibi\Exception;
use FOL\Model\ORM\ChatService;
use LoginFormComponent;
use Nette\Application\AbortException;
use Nette\Mail\IMailer;
use RecoverFormComponent;

class DefaultPresenter extends BasePresenter {

    protected ChatService $chatService;

    protected TeamAuthenticator $authenticator;

    protected IMailer $mailer;

    public function injectSecondary(IMailer $mailer, TeamAuthenticator $authenticator, ChatService $chatService): void {
        $this->mailer = $mailer;
        $this->authenticator = $authenticator;
        $this->chatService = $chatService;
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function actionLogout(): void {
        $this->getUser()->logout();
        $this->redirect("default");
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderChat(): void {
        $this->getComponent("chat")->setSource($this->chatService->findAll($this->lang));
        $this->setPageTitle(_("Diskuse (česká verze)"));
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderDefault(): void {
        $this->setPagetitle(_("Mezinárodní soutež ve fyzice"));
        $this->template->year = $this->yearsService->findCurrent();
        $this->changeViewByLang();
    }

    public function renderLastYears(): void {
        $this->setPagetitle(_("Minulé ročníky"));
        $this->changeViewByLang();
    }

    public function renderLogin(): void {
        $this->setPagetitle(_("Přihlásit se"));
    }

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    public function renderRecover(): void {
        $this->setPageTitle(_("Obnova hesla"));
        if (!$this->yearsService->isGameMigrated()) {
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
        return new ChatListComponent($this->getContext());
    }

    protected function createComponentLogin(): LoginFormComponent {
        return new LoginFormComponent($this->getContext(),$this->authenticator);
    }

    protected function createComponentRecover(): RecoverFormComponent {
        return new RecoverFormComponent($this->getContext());
    }
}
