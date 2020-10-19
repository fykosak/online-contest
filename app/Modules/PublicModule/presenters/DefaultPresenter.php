<?php

namespace FOL\Modules\PublicModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use ChatListComponent;
use Dibi\Exception;
use FOL\Model\ORM\ChatService;
use Nette\Mail\IMailer;

class DefaultPresenter extends BasePresenter {

    protected ChatService $chatService;

    protected TeamAuthenticator $authenticator;

    protected IMailer $mailer;

    public function injectSecondary(IMailer $mailer, TeamAuthenticator $authenticator, ChatService $chatService): void {
        $this->mailer = $mailer;
        $this->authenticator = $authenticator;
        $this->chatService = $chatService;
    }

    protected function startUp(): void {
        parent::startUp();
        if ($this->getAction() === 'chat') {
            $this->forward(':Game:Chat:default');
        }
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
}
