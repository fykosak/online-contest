<?php

namespace FOL\Components;

use DataNotFoundException;
use FOL\tools\Helpers;
use Nette\Application\UI\Control;
use Nette\Application\UI\Template;
use Nette\DI\Container;
use Nette\Localization\Translator;

abstract class BaseComponent extends Control {

    protected Container $container;

    protected Translator $translator;

    public function __construct(Container $container) {
        $this->container = $container;
        $container->callInjects($this);
        $this->startUp();
    }

    protected function getContext(): Container {
        return $this->container;
    }

    public function injectTranslator(Translator $translator): void {
        $this->translator = $translator;
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    /**
     * @return Template
     */
    protected function createTemplate(): Template  {
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        $template->getLatte()->addFilter("date2", Helpers::getHelper('date')); // this shadows standard Nette helper
        $template->getLatte()->addFilter("time", Helpers::getHelper('time'));
        $template->getLatte()->addFilter("timeOnly", Helpers::getHelper('timeOnly'));
        $template->getLatte()->addFilter("texy", Helpers::getHelper('texy'));
        $template->getLatte()->addFilter("i18n", Helpers::getHelper('i18n'));
        return $template;
    }

    protected function beforeRender(): void {
    }

    protected function startUp(): void {
    }
}
