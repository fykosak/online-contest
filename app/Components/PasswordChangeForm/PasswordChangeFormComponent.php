<?php

namespace FOL\Components\PasswordChangeForm;

use Dibi\Exception;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\TeamsService;
use FOL\Components\BaseForm;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Components\BaseComponent;
use Nette\DI\Container;

class PasswordChangeFormComponent extends BaseComponent {

    protected TeamsService $teamsService;
    private ModelTeam $team;

    public function __construct(Container $container, ModelTeam $team) {
        parent::__construct($container);
        $this->team = $team;
    }

    public function injectTeamsService(TeamsService $teamsService): void {
        $this->teamsService = $teamsService;
    }

    /**
     * @param Form $form
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    private function formSubmitted(Form $form): void {
        $values = $form->getValues();
        $changes = [
            'password' => TeamAuthenticator::passwordHash($values['password']),
        ];
        $this->teamsService->update($changes)->where('[id_team] = %i', $this->team->id_team)->execute();

        $this->getPresenter()->flashMessage(_('Heslo bylo změněno.'), 'info');
        $this->getPresenter()->redirect('Team:default');
    }

    // ---- PROTECTED METHODS

    protected function createComponentForm(): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addPassword('password', 'Nové heslo')
            ->setRequired(true)
            ->addRule(Form::FILLED, 'Heslo musí být vyplněno.');

        $form->addPassword('passwordCheck', 'Nové heslo (pro kontrolu)')
            ->setRequired(true)
            ->addRule(Form::EQUAL, 'Hesla se neshodují', $form['password'])
            ->setOmitted();

        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = function (Form $form) {
            $this->formSubmitted($form);
        };

        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'passwordChangeForm.latte');
        parent::render();
    }
}
