<?php

namespace FOL\Modules\PublicModule;

use FOL\Model\Authentication\TeamAuthenticator;
use Dibi\Exception;
use FOL\Model\ORM\TeamsService;
use FOL\Components\PasswordChangeForm\PasswordChangeFormComponent;
use FOL\Components\TeamList\TeamListComponent;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Http\IResponse;
use Nette\Http\Url;
use Nette\Security\AuthenticationException;
use Nette\Utils\Html;

class TeamPresenter extends BasePresenter {

    protected TeamsService $teamsService;

    protected TeamAuthenticator $authenticator;

    public function injectSecondary(TeamAuthenticator $teamAuthenticator, TeamsService $teamsService): void {
        $this->authenticator = $teamAuthenticator;
        $this->teamsService = $teamsService;
    }

    /**
     * @return void
     * @throws AbortException
     * @throws Exception
     */
    public function actionRegistration(): void {
        if (!$this->getCurrentYear()->isRegistrationActive()) {
            $this->flashMessage(_('Registrace není aktivní.'), 'danger');
            $this->redirect('Default:default');
        } elseif ($url = $this->getRegistrationValue('url')) {
            $uri = new Url($url);
            $uri->appendQuery(['lang' => $this->lang]);
            $this->redirectUrl($uri, IResponse::S307_TEMPORARY_REDIRECT);
        }
    }

    /**
     * @param null $token
     * @return void
     * @throws Exception
     * @throws AbortException
     * @throws BadRequestException
     */
    public function renderChangePassword($token = null): void {
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
     * @throws Exception
     * @throws AbortException
     */
    public function renderDefault(): void {
        $team = $this->getPresenter()->getLoggedTeam2();
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

    /**
     * @return void
     * @throws Exception
     */
    public function renderList(): void {
        $this->setPageTitle(_('Seznam týmů'));
        $this->getComponent('teamList')->setSource(
            $this->teamsService->findAll()
        );
        $this->template->categories = $this->teamsService->getCategoryNames();
    }

    public function renderRegistration(): void {
        $this->setPageTitle(_('Registrace'));
        $this->flashMessage(_('Registrace nového týmu je možná jen přes FKSDB.'), 'warning');
    }

    // ---- PROTECTED METHODS
    protected function createComponentTeamList(): TeamListComponent {
        return new TeamListComponent($this->getContext());
    }

    protected function createComponentPasswordChangeForm(): PasswordChangeFormComponent {
        return new PasswordChangeFormComponent($this->getContext());
    }

    // ---- PRIVATE METHODS
    private function getRegistrationValue($key) {
        $registration = $this->context->parameters['registration'];
        return $registration[$key];
    }

}
