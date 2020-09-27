<?php

use FOL\Model\ORM\CompetitorsService;
use FOL\Model\ORM\TeamsService;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\Message;
use Nette\Mail\IMailer;
use Nette\Http\IRequest;
use App\Model\Authentication\TeamAuthenticator;

class RecoverFormComponent extends BaseComponent {

    private TeamAuthenticator $authenticator;

    private IMailer $mailer;

    private IRequest $httpRequest;

    protected TeamsService $teamsService;

    protected CompetitorsService $competitorsService;

    public function injectPrimary(
        TeamAuthenticator $authenticator,
        IMailer $mailer,
        IRequest $httpRequest,
        TeamsService $teamsService,
        CompetitorsService $competitorsService
    ) {
        $this->authenticator = $authenticator;
        $this->mailer = $mailer;
        $this->httpRequest = $httpRequest;
        $this->teamsService = $teamsService;
        $this->competitorsService = $competitorsService;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws InvalidLinkException
     * @throws \Dibi\Exception
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $team = $this->teamsService->findByEmail($values['email']);
        if (!$team) {
            $this->getPresenter()->flashMessage(_("Tým nenalezen."), "danger");
            $this->getPresenter()->redirect("Default:default");
        }

        $competitors = $this->competitorsService->findAllByTeam($team['id_team']);
        $token = $this->authenticator->createRecoveryToken($team['id_team']);

        if (is_null($token)) {
            $this->getPresenter()->flashMessage(_("Tým se již pokouší o obnovu hesla."), "danger");
            $this->getPresenter()->redirect("Default:default");
        }

        $message = new Message;
        $prefs = $this->getPresenter()->context->parameters['mail'];

        //this way it works behind reverse proxy, but is ugly
        $recoveryUrl = "https://" . $this->httpRequest->getRemoteHost() . $this->getPresenter()->link("Team:changePassword", ['token' => $token]);

        $message->setFrom($prefs['info'], $prefs['name'])
            ->setSubject(_("[Fyziklání online] obnova hesla"))
            ->setBody(sprintf(_("Pro obnovu hesla přejděte na %s\nVaši organizátoři\n\nToto je automatická zpráva. Pokud jste o obnovu hesla nežádali, tento e-mail ignorujte."), $recoveryUrl));

        foreach ($competitors as $competitor) {
            $message->addTo($competitor['email']);
        }
        $this->mailer->send($message);
        $this->getPresenter()->flashMessage(_("E-mail pro obnovu byl odeslán."), "info");
        $this->getPresenter()->redirect("Default:default");
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addText("email", "E-mail libovolného člena týmu")
            ->addRule(Form::FILLED, "E-mail musí být vyplněn.")
            ->addRule(Form::EMAIL, "E-mail nemá správný formát.");

        $form->addSubmit("recover", "Pokračovat");
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'recoverForm.latte');
        parent::render();
    }

}
