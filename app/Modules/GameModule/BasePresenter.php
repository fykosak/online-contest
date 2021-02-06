<?php

namespace FOL\Modules\GameModule;

use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\AbortException;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    /**
     * @return void
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    protected function startUp(): void {
        parent::startUp();
        if (is_null($this->getLoggedTeam())) {
            $this->flashMessage(_('Do této sekce mají přístup pouze přihlášené týmy.'), 'danger');
            $this->redirect(':Game:Auth:login');
        }
    }

    protected function createComponentNavigation(): Navigation {
        $navigation = parent::createComponentNavigation();
        $navigation->addNavItem(new NavItem(':Game:Task:default', [], _('Zadání'), 'visible-sm-inline glyphicon glyphicon-compressed'));
        $navigation->addNavItem(new NavItem(':Game:Card:List', [], _('Cards'), 'visible-sm-inline glyphicon glyphicon-compressed'));
        $navigation->addNavItem(new NavItem(':Game:Noticeboard:default', [], _('Nástěnka'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
        $navigation->addNavItem(new NavItem(':Game:Chat:default', [], _('Chat'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
        if ($this->getCurrentYear()->isGameStarted()) {
            $navigation->addNavItem(new NavItem(':Game:Answer:history', [], _('Historie'), ''));
            $navigation->addNavItem(new NavItem(':Game:Results:detail', [], _('Výsledky'), 'visible-sm-inline glyphicon glyphicon-stats', [
                new NavItem(':Game:Results:default', [], _('Results'), 'visible-sm-inline glyphicon glyphicon-stats'),
                new NavItem(':Game:Results:detail', [], _('Detail results'), 'visible-sm-inline glyphicon glyphicon-stats'),
            ]));
        }

        if ($this->getCurrentYear()->isGameEnd()) {
            //  $navigation->addNavItem(new NavItem('{$basePath}/download/2019-1/ulohy/reseni.{$lang}.pdf', [], _('Historie'), ''));
        }
        //    <a href="{$basePath}/download/2019-1/ulohy/reseni.{$lang}.pdf">{_'Řešení'}</a>
        $navigation->addNavItem(new NavItem(':Game:Auth:logout', [], _('Odhlásit se'), 'visible-sm-inline glyphicon glyphicon-log-out'));

        return $navigation;
    }
}
