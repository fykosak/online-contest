<?php

namespace FOL\Modules\PublicModule;

use FOL\Model\Authentication\TeamAuthenticator;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Components\PasswordChangeForm\PasswordChangeFormComponent;
use FOL\Components\TeamList\TeamListComponent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;
use Nette\Utils\Html;

class TeamPresenter extends BasePresenter {

    protected TeamAuthenticator $authenticator;
    protected ServiceTeam $serviceTeam;

    public function injectSecondary(TeamAuthenticator $teamAuthenticator, ServiceTeam $serviceTeam): void {
        $this->authenticator = $teamAuthenticator;
        $this->serviceTeam = $serviceTeam;
    }

    /**
     * @param string|null $token
     * @return void
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderChangePassword(?string $token = null): void {
        if (!is_null($token)) {
            try {
                $this->authenticator->authenticateByToken($token);
            } catch (AuthenticationException $e) {
                $this->error(_('Chybný token.'), IResponse::S401_UNAUTHORIZED);
            }
        }
        if (!$this->user->isAllowed('team', 'edit')) {
            $this->flashMessage(_('Nejprve se prosím přihlaste.'), 'danger');
            $this->redirect('Default:login');
        }

        $this->setPageTitle(_('Změna hesla'));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function renderDefault(): void {
        $team = $this->getPresenter()->getLoggedTeam();
        if (!$team) {
            $this->redirect('Default:default');
        }
        $this->setPageTitle($team->name);
    }

    public function renderList(): void {
        $this->setPageTitle(_('Seznam týmů'));
        $this->template->categories = ServiceTeam::getCategoryNames();
    }

    protected function createComponentTeamList(): TeamListComponent {
        return new TeamListComponent($this->getContext());
    }

    protected function createComponentPasswordChangeForm(): PasswordChangeFormComponent {
        return new PasswordChangeFormComponent($this->getContext(), $this->getLoggedTeam());
    }
}
