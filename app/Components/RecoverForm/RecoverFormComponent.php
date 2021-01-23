<?php

namespace FOL\Components\RecoverForm;

use FOL\Model\ORM\Services\ServiceCompetitor;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseForm;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\Message;
use Nette\Mail\Mailer;
use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\BaseComponent;

class RecoverFormComponent extends BaseComponent {

    private TeamAuthenticator $authenticator;
    private Mailer $mailer;
    protected TeamsService $teamsService;
    protected ServiceCompetitor $serviceCompetitors;

    public function injectPrimary(
        TeamAuthenticator $authenticator,
        Mailer $mailer,
        TeamsService $teamsService,
        ServiceCompetitor $serviceCompetitors
    ): void {
        $this->authenticator = $authenticator;
        $this->mailer = $mailer;
        $this->teamsService = $teamsService;
        $this->serviceCompetitors = $serviceCompetitors;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     * @throws InvalidLinkException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();

        $competitor = $this->serviceCompetitors->findByEmail($values['email']);
        if (!$competitor) {
            $this->getPresenter()->flashMessage(_('Tým nenalezen.'), 'danger');
            $this->getPresenter()->redirect('Default:default');
        }
        $team = $competitor->getTeam();
        $competitors = $this->serviceCompetitors->findAllByTeam($team);
        $token = $this->authenticator->createRecoveryToken($team);

        if (is_null($token)) {
            $this->getPresenter()->flashMessage(_('Tým se již pokouší o obnovu hesla.'), 'danger');
            $this->getPresenter()->redirect('Default:default');
        }

        $message = new Message();
        $prefs = $this->getPresenter()->context->parameters['mail'];

        //this way it works behind reverse proxy, but is ugly
        $recoveryUrl = $this->getPresenter()->link('//:Public:Team:changePassword', ['token' => $token]);

        $message->setFrom($prefs['info'], $prefs['name'])
            ->setSubject(_('[Fyziklání online] obnova hesla'))
            ->setBody(sprintf(_('Pro obnovu hesla přejděte na %s\nVaši organizátoři\n\nToto je automatická zpráva. Pokud jste o obnovu hesla nežádali, tento e-mail ignorujte.'), $recoveryUrl));

        foreach ($competitors as $competitor) {
            $message->addTo($competitor['email']);
        }
        $this->mailer->send($message);
        $this->getPresenter()->flashMessage(_('E-mail pro obnovu byl odeslán.'));
        $this->getPresenter()->redirect('Default:default');
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addText('email', 'E-mail libovolného člena týmu')
            ->addRule(Form::FILLED, 'E-mail musí být vyplněn.')
            ->addRule(Form::EMAIL, 'E-mail nemá správný formát.');

        $form->addSubmit('recover', 'Pokračovat');
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
