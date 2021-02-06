<?php

namespace FOL\Modules\OrgModule;

use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    protected function startUp(): void {
        if (!$this->user->isInRole('org')) {
            $this->redirect(':Org:Auth:login');
        }
        parent::startUp();
    }

    protected function createComponentNavigation(): Navigation {
        $navigation = parent::createComponentNavigation();
        $navigation->addNavItem(new NavItem(':Org:Noticeboard:add', [], _('Notifikace'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Chat:default', [], _('Chat'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Default:answerStats', [], _('Pokusy o odevzdání'), 'visible-sm-inline glyphicon glyphicon-comment'));

        $navigation->addNavItem(new NavItem(':Org:Default:statsDetail', [], _('Výsledky'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Default:answerStats', [], _('Podrobné výsledky'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Default:statsTasks', [], _('Statistiky úkolů'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Game:Auth:logout', [], _('Logout'), 'visible-sm-inline glyphicon glyphicon-comment'));
        return $navigation;
    }
}
