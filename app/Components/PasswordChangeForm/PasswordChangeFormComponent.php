<?php

namespace FOL\Components\PasswordChangeForm;

use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\BaseForm;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\BaseComponent;
use Nette\DI\Container;

final class PasswordChangeFormComponent extends BaseComponent {

    private ModelTeam $team;
    private ServiceTeam $serviceTeam;

    public function __construct(Container $container, ModelTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    public function injectTeamsService(ServiceTeam $serviceTeam): void {
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * @param Form $form
     * @throws AbortException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $this->serviceTeam->updateModel2($this->team, [
            'password' => TeamAuthenticator::passwordHash($values['password']),
        ]);

        $this->getPresenter()->flashMessage(sprintf(_('Heslo pro tým %s bylo změněno. Nyní se prosím přihlašte s novým heslem.'), $this->team->name));
        $this->getPresenter()->redirect(':Game:Auth:login');
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addPassword('password', _('Nové heslo'))
            ->setRequired(true)
            ->addRule(Form::FILLED, _('Heslo musí být vyplněno.'));

        $form->addPassword('passwordCheck', _('Nové heslo (pro kontrolu)'))
            ->setRequired(true)
            ->addRule(Form::EQUAL, _('Hesla se neshodují'), $form['password'])
            ->setOmitted();

        $form->addSubmit('submit', _('Odeslat'));
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    public function render(): void {
        $this->template->teamName = $this->team->name;
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'passwordChangeForm.latte');
        parent::render();
    }
}
