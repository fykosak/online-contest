<?php

namespace FOL\Components;

use App\Tools\Helpers;
use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\DI\Container;
use Nette\Localization\ITranslator;

abstract class BaseComponent extends Control {

    protected Container $container;

    protected ITranslator $translator;

    public function __construct(Container $container) {
        parent::__construct();
        $this->container = $container;
        $container->callInjects($this);
        $this->startUp();
    }

    protected function getContext(): Container {
        return $this->container;
    }

    public function injectTranslator(ITranslator $translator): void {
        $this->translator = $translator;
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    /**
     * @return ITemplate
     * @throws \DataNotFoundException
     */
    protected function createTemplate(): ITemplate {
        /** @var Template $template */
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        $template->getLatte()->addFilter("date2", Helpers::getHelper('date')); // this shadows standard Nette helper
        $template->getLatte()->addFilter("time", Helpers::getHelper('time'));
        $template->getLatte()->addFilter("timeOnly", Helpers::getHelper('timeOnly'));
        $template->getLatte()->addFilter("texy", Helpers::getHelper('texy'));
        return $template;
    }

    protected function beforeRender(): void {
    }

    protected function startUp(): void {
    }
}
