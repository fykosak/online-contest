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
    public function actionRegistration(): void {
        if ($url = $this->getRegistrationValue('url')) {
            $uri = new Url($url);
            $uri->appendQuery(['lang' => $this->lang]);
            $this->redirectUrl($uri, IResponse::S307_TEMPORARY_REDIRECT);
        }
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
        $url = $this->getRegistrationValue('editUrl');
        if ($url) {
            $uri = new Url(sprintf($url, $team->id_team));
            $uri->appendQuery(['lang' => $this->lang]);
            $link = Html::el('a', _('na stránce přihlášky'))->href($uri);
            $message = Html::el();
            $message->addText(_('Editaci přihlášky provádějte po osobním přihlášení '));
            $message->addHtml($link);
            $message->addText('.');
            $this->flashMessage($message);
            $this->template->external = true;
        } else {
            $this->template->external = false;
        }
    }

    public function renderList(): void {
        $this->setPageTitle(_('Seznam týmů'));
        $this->template->categories = ServiceTeam::getCategoryNames();
    }

    public function renderRegistration(): void {
        $this->setPageTitle(_('Registrace'));
        $this->flashMessage(_('Registrace nového týmu je možná jen přes FKSDB.'), 'warning');
    }

    protected function createComponentTeamList(): TeamListComponent {
        return new TeamListComponent($this->getContext());
    }

    protected function createComponentPasswordChangeForm(): PasswordChangeFormComponent {
        return new PasswordChangeFormComponent($this->getContext(), $this->getLoggedTeam());
    }

    /**
     * @param mixed $key
     * @return mixed
     */
    private function getRegistrationValue($key) {
        $registration = $this->context->parameters['registration'];
        return $registration[$key];
    }

}
