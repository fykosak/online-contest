<?php

use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\Message;
use Nette\Mail\IMailer;
use Nette\Http\IRequest;
use App\Model\Interlos;
use App\Model\Authentication\TeamAuthenticator;

class RecoverFormComponent extends BaseComponent {

    private TeamAuthenticator $authenticator;

    private IMailer $mailer;

    private IRequest $httpRequest;

    public function __construct(TeamAuthenticator $authenticator, IMailer $mailer, IRequest $httpRequest) {
        parent::__construct();
        $this->authenticator = $authenticator;
        $this->mailer = $mailer;
        $this->httpRequest = $httpRequest;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws InvalidLinkException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $team = Interlos::teams()->findByEmail($values['email']);
        if (!$team) {
            $this->getPresenter()->flashMessage(_("Tým nenalezen."), "danger");
            $this->getPresenter()->redirect("Default:default");
        }

        $competitors = Interlos::competitors()->findAllByTeam($team['id_team']);
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
        $form = new BaseForm();

        $form->addText("email", "E-mail libovolného člena týmu")
            ->addRule(Form::FILLED, "E-mail musí být vyplněn.")
            ->addRule(Form::EMAIL, "E-mail nemá správný formát.");

        $form->addSubmit("recover", "Pokračovat");
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

}
