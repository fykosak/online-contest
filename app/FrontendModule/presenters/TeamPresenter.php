<?php

class Frontend_TeamPresenter extends Frontend_BasePresenter {

    public function actionRegistration() {
        if (!Interlos::isRegistrationActive()) {
            $this->flashMessage(_("Registrace není aktivní."), "error");
            $this->redirect("Default:default");
        } else if ($url = $this->getRegistrationValue('url')) {
            $uri = new Uri($url);
            $uri->appendQuery(array('lang' => $this->lang));
            $this->redirectUri($uri, IHttpResponse::S307_TEMPORARY_REDIRECT);
        }
    }

    public function renderDefault() {
        $team = Interlos::getLoggedTeam();
        if (!$team) {
            $this->redirect("Default:default");
        }
        $this->setPageTitle($team->name);
        $url = $this->getRegistrationValue('editUrl');
        if ($url) {
            $uri = new Uri(sprintf($url, $team->id_team));
            $uri->appendQuery(array('lang' => $this->lang));
            $link = Html::el('a', _('na stránce přihlášky'))->href($uri);
            $message = Html::el();
            $message->add(_('Editaci přihlášky provádějte po přihlášení '));
            $message->add($link);
            $message->add('.');
            $this->flashMessage($message);
            $this->getTemplate()->external = true;
        } else {
            $this->getTemplate()->external = false;
        }
    }

    public function renderList() {
        $this->setPageTitle(_("Seznam týmů"));
        $this->getComponent("teamList")->setSource(
                Interlos::teams()->findAll()
        );
        $this->getTemplate()->categories = Interlos::teams()->getCategoryNames();
    }

    public function renderRegistration() {
        $this->setPageTitle(_("Registrace"));
    }

    // ---- PROTECTED METHODS

    protected function createComponentTeamForm($name) {
        return new TeamFormComponent($this, $name);
    }

    protected function createComponentTeamList($name) {
        return new TeamListComponent($this, $name);
    }

    // ---- PRIVATE METHODS
    private function getRegistrationValue($key) {
        $registration = Environment::getConfig('registration');
        return $registration[$key];
    }

}
