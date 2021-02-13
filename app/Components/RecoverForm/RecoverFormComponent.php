<?php

namespace FOL\Components\RecoverForm;

use FOL\Model\ORM\Services\ServiceCompetitor;
use FOL\Components\BaseForm;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Application\UI\InvalidLinkException;
use Nette\Mail\Message;
use Nette\Mail\Mailer;
use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\BaseComponent;

final class RecoverFormComponent extends BaseComponent {

    private TeamAuthenticator $authenticator;
    private Mailer $mailer;
    private ServiceCompetitor $serviceCompetitors;

    public function injectPrimary(
        TeamAuthenticator $authenticator,
        Mailer $mailer,
        ServiceCompetitor $serviceCompetitors
    ): void {
        $this->authenticator = $authenticator;
        $this->mailer = $mailer;
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
            $this->getPresenter()->redirect(':Game:Auth:login');
        }
        $team = $competitor->getTeam();
        $competitors = $team->getCompetitors();
        $token = $this->authenticator->createRecoveryToken($team);

        if (is_null($token)) {
            $this->getPresenter()->flashMessage(_('Tým se již pokouší o obnovu hesla.'), 'danger');
            $this->getPresenter()->redirect(':Game:Auth:login');
        }

        $message = new Message();
        $prefs = $this->getPresenter()->context->parameters['mail'];

        //this way it works behind reverse proxy, but is ugly
        $recoveryUrl = $this->getPresenter()->link('//:Game:Auth:changePassword', ['token' => $token->token]);

        $message->setFrom($prefs['info'], $prefs['name'])
            ->setSubject(_('Obnova hesla - Fyziklání 2021'))
            ->setBody(sprintf(_('Člen vašeho týmu %2$s požádal o obnovu hesla. Pro jeho obnovu přejděte na %1$s'), $recoveryUrl, $team->name));

        foreach ($competitors as $competitor) {
            $message->addTo($competitor['email']);
        }
        $this->mailer->send($message);
        $this->getPresenter()->flashMessage(_('Všem členům vašeho týmu byl odeslán email s odkazem na změnu hesla.'));
        $this->getPresenter()->redirect(':Game:Auth:login');
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
