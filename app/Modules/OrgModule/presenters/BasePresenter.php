<?php

namespace FOL\Modules\OrgModule\Presenters;

use Dibi\Exception;
use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    protected function createComponentNavigation(): Navigation {
        $navigation = parent::createComponentNavigation();
        $navigation->addNavItem(new NavItem(':Org:Noticeboard:add', [], _('Přidat notifikaci'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Chat:default', [], _('Přidat příspěvek do fóra.'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Org:answerStats', [], _('pokusy o odevzdání'), 'visible-sm-inline glyphicon glyphicon-comment'));

        $navigation->addNavItem(new NavItem(':Org:Org:statsDetail', [], _('výsledky'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Org:answerStats', [], _('podrobné výsledky'), 'visible-sm-inline glyphicon glyphicon-comment'));
        $navigation->addNavItem(new NavItem(':Org:Org:statsTasks', [], _('statistiky úkolů'), 'visible-sm-inline glyphicon glyphicon-comment'));

        return $navigation;
    }
}
