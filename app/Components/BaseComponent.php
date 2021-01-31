<?php

namespace FOL\Components;

use FOL\Model\ORM\Services\ServiceLog;
use FOL\Tools\Helpers;
use Nette\Application\UI\Template;
use Nette\DI\Container;

abstract class BaseComponent extends \Fykosak\Utils\BaseComponent\BaseComponent {

    protected ServiceLog $serviceLog;

    public function __construct(Container $container) {
        parent::__construct($container);
        $this->startUp();
    }

    public function injectServiceLog(ServiceLog $serviceLog): void {
        $this->serviceLog = $serviceLog;
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    protected function createTemplate(): Template {
        $template = parent::createTemplate();
        $template->getLatte()->addFilter('date2', Helpers::getHelper('date')); // this shadows standard Nette helper
        $template->getLatte()->addFilter('time', Helpers::getHelper('time'));
        $template->getLatte()->addFilter('timeOnly', Helpers::getHelper('timeOnly'));
        $template->getLatte()->addFilter('texy', Helpers::getHelper('texy'));
        $template->getLatte()->addFilter('i18n', Helpers::getHelper('i18n'));
        return $template;
    }

    protected function beforeRender(): void {
    }

    protected function startUp(): void {
    }
}
