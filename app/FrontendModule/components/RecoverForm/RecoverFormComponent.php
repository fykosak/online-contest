<?php

use Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    Nette\Security,
    Nette\Mail\Message,
    Nette\Mail\IMailer,
    Nette\Http\IRequest,
    App\Model\Interlos,
    App\Model\Authentication\TeamAuthenticator;

class RecoverFormComponent extends BaseComponent {
    
    /** @var App\Model\Authentication\TeamAuthenticator */
    private $authenticator;

    /** @var IMailer */
    private $mailer;

    /** @var IRequest */
    private $httpRequest;

    public function __construct(TeamAuthenticator $authenticator, IMailer $mailer, IRequest $httpRequest, IContainer $parent = null, $name = null) {
        parent::__construct($parent, $name);
        $this->authenticator = $authenticator;
        $this->mailer = $mailer;
        $this->httpRequest = $httpRequest;
    }

    public function formSubmitted(Form $form) {
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
        $recoveryUrl = "https://".$this->httpRequest->getRemoteHost().$this->getPresenter()->link("Team:changePassword", ['token' => $token]);

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

    protected function createComponentForm($name) {
        $form = new BaseForm($this, $name);

        $form->addText("email", "E-mail libovolného člena týmu")
            ->addRule(Form::FILLED, "E-mail musí být vyplněn.")
            ->addRule(Form::EMAIL, "E-mail nemá správný formát.");

        $form->addSubmit("recover", "Pokračovat");
        $form->onSuccess[] = array($this, "formSubmitted");

        return $form;
    }

}
