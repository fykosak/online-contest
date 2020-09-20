<?php

namespace App\FrontendModule\Presenters;

use App\Model\Authentication\TeamAuthenticator;
use App\Model\Interlos;
use Nette\Http\Url;
use Nette\Utils;
use Nette\Http;
use Nette\Security\AuthenticationException;
use PasswordChangeFormComponent;
use TeamFormComponent;
use TeamListComponent;

class TeamPresenter extends BasePresenter {

    protected TeamAuthenticator $authenticator;

    public function injectSecondary(TeamAuthenticator $teamAuthenticator): void {
        $this->authenticator = $teamAuthenticator;
    }

    public function actionRegistration(): void {
        if (!Interlos::isRegistrationActive()) {
            $this->flashMessage(_("Registrace není aktivní."), "danger");
            $this->redirect("Default:default");
        } elseif ($url = $this->getRegistrationValue('url')) {
            $uri = new Url($url);
            $uri->appendQuery(['lang' => $this->lang]);
            $this->redirectUrl($uri, Http\IResponse::S307_TEMPORARY_REDIRECT);
        }
    }

    public function renderChangePassword($token = null): void {
        if (!is_null($token)) {
            try {
                $this->authenticator->authenticateByToken($token);
            } catch (AuthenticationException $e) {
                $this->error(_("Chybný token."), Http\IResponse::S401_UNAUTHORIZED);
            }
        }
        if (!$this->user->isAllowed('team', 'edit')) {
            $this->flashMessage(_("Nejprve se prosím přihlaste."), "danger");
            $this->redirect("Default:login");
        }

        $this->setPageTitle(_("Změna hesla"));
    }

    public function renderDefault(): void {
        $team = Interlos::getLoggedTeam($this->user);
        if (!$team) {
            $this->redirect("Default:default");
        }
        $this->setPageTitle($team->name);
        $url = $this->getRegistrationValue('editUrl');
        if ($url) {
            $uri = new Url(sprintf($url, $team->id_team));
            $uri->appendQuery(['lang' => $this->lang]);
            $link = Utils\Html::el('a', _('na stránce přihlášky'))->href($uri);
            $message = Utils\Html::el();
            $message->addText(_('Editaci přihlášky provádějte po osobním přihlášení '));
            $message->addHtml($link);
            $message->addText('.');
            $this->flashMessage($message);
            $this->getTemplate()->external = true;
        } else {
            $this->getTemplate()->external = false;
        }
    }

    public function renderList(): void {
        $this->setPageTitle(_("Seznam týmů"));
        $this->getComponent("teamList")->setSource(
            Interlos::teams()->findAll()
        );
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

    public function renderRegistration(): void {
        $this->setPageTitle(_("Registrace"));
        $this->flashMessage(_('Registrace nového týmu je možná jen přes FKSDB.'), 'warning');
    }

    // ---- PROTECTED METHODS

    protected function createComponentTeamForm(): TeamFormComponent {
        return new TeamFormComponent();
    }

    protected function createComponentTeamList(): TeamListComponent {
        return new TeamListComponent();
    }

    protected function createComponentPasswordChangeForm(): PasswordChangeFormComponent {
        return new PasswordChangeFormComponent();
    }

    // ---- PRIVATE METHODS
    private function getRegistrationValue($key) {
        $registration = $this->context->parameters['registration'];
        return $registration[$key];
    }

}
