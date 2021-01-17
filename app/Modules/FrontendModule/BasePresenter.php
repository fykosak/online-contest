<?php

namespace FOL\Modules\FrontendModule;

use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    protected function createComponentNavigation(): Navigation {
        $navigation = parent::createComponentNavigation();

        $navigation->addNavItem(new NavItem(':Public:Default:lastYears', [], _('Archiv'), 'visible-sm-inline glyphicon glyphicon-compressed'));
        $navigation->addNavItem(new NavItem(':Public:Default:rules', [], _('Pravidla'), 'visible-sm-inline glyphicon glyphicon-exclamation-sign'));
        $navigation->addNavItem(new NavItem(':Public:Default:faq', [], _('FAQ'), 'visible-sm-inline glyphicon glyphicon-question-sign'));
        $navigation->addNavItem(new NavItem(':Public:Default:howto', [], _('Návod'), 'visible-sm-inline glyphicon glyphicon-info-sign'));

        if ($this->getCurrentYear()->isRegistrationStarted()) {
            $navigation->addNavItem(new NavItem(':Public:Default:chat', [], _('Fórum'), 'visible-sm-inline glyphicon glyphicon-comment'));
            $navigation->addNavItem(new NavItem(':Public:Team:list', [], _('Týmy'), 'visible-sm-inline glyphicon glyphicon-list'));
            if ($this->getCurrentYear()->isGameStarted()) {
                $navigation->addNavItem(new NavItem(':Public:Stats:default', [], _('Výsledky'), 'visible-sm-inline glyphicon glyphicon-stats'));
                $navigation->addNavItem(new NavItem(':Frontend:Noticeboard:default', [], _('Nástěnka'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
                if ($this->getUser()->isLoggedIn()) {
                    $navigation->addNavItem(new NavItem(':Game:Game:default', [], _('Hra'), 'visible-sm-inline glyphicon glyphicon-tower'));
                }
            }
        }

        if ($this->getCurrentYear()->isRegistrationActive()) {
            if (!$this->getUser()->isLoggedIn()) {
                $navigation->addNavItem(new NavItem(':Public:Team:registration', [], _('Registrace'), 'visible - sm - inline glyphicon glyphicon-edit'));
            }
        }
        if ($this->getCurrentYear()->isRegistrationStarted()) {
            if (!$this->getUser()->isLoggedIn()) {
                $navigation->addNavItem(new NavItem(':Public:Auth:login', [], _('Přihlásit se'), 'visible-sm-inline glyphicon glyphicon-log-in'));
            } else {
                $navigation->addNavItem(new NavItem(':Public:Auth:logout', [], _('Odhlásit se'), 'visible-sm-inline glyphicon glyphicon-log-out'));
            }
        }
        return $navigation;
    }
}
