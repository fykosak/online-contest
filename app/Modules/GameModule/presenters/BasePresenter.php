<?php

namespace FOL\Modules\GameModule\Presenters;

use Dibi\Exception;
use FOL\Components\Navigation\Navigation;
use FOL\Components\Navigation\NavItem;
use FOL\Model\ORM\Model\ModelInstance;
use FOL\Model\ORM\Service\ServiceInstance;
use Nette\Application\AbortException;

abstract class BasePresenter extends \FOL\Modules\Core\BasePresenter {

    protected ModelInstance $instance;

    protected ServiceInstance $serviceInstance;

    public function injectServiceInstance(ServiceInstance $serviceInstance): void {
        $this->serviceInstance = $serviceInstance;
    }

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    protected function startUp(): void {
        parent::startUp();
        if (is_null($this->getLoggedTeam())) {
            $this->flashMessage(_('Do této sekce mají přístup pouze přihlášené týmy.'), 'danger');
            $this->redirect(':Public:Default:default');
        }
        /** @var Navigation $navigation */
        $navigation = $this->getComponent('navigation');

        $navigation->addNavItem(new NavItem(':Game:Game:default', [], _('Zadání'), 'visible-sm-inline glyphicon glyphicon-compressed'));

        if ($this->yearsService->isGameActive()) {
            $navigation->addNavItem(new NavItem(':Game:Game:answer', [], _('Odevzdat řešení'), ''));
            $navigation->addNavItem(new NavItem(':Game:Game:skip', [], _('Přeskočit úkol'), ''));
        }
        $navigation->addNavItem(new NavItem(':Game:Noticeboard:default', [], _('Nástěnka'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
        $navigation->addNavItem(new NavItem(':Game:Chat:default', [], _('Chat'), 'visible-sm-inline glyphicon glyphicon-pushpin'));
        $navigation->addNavItem(new NavItem(':Game:Game:history', [], _('Historie'), ''));

        if ($this->yearsService->isGameEnd()) {
            //  $navigation->addNavItem(new NavItem('{$basePath}/download/2019-1/ulohy/reseni.{$lang}.pdf', [], _('Historie'), ''));
        }

        //    <a href="{$basePath}/download/2019-1/ulohy/reseni.{$lang}.pdf">{_'Řešení'}</a>
        $navigation->addNavItem(new NavItem(':Public:Auth:logout', [], _('Odhlásit se'), 'visible-sm-inline glyphicon glyphicon-log-out'));
    }

    protected function createComponentNavigation(): Navigation {
        return new Navigation($this->getContext());
    }
}
