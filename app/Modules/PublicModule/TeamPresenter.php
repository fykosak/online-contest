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
