<?php

use App\Tools\InterlosTemplate;
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

    public function injectTranslator(ITranslator $translator) {
        $this->translator = $translator;
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    /**
     * @return ITemplate
     * @throws DataNotFoundException
     * @throws NullPointerException
     */
    protected function createTemplate(): ITemplate {
        /** @var Template $template */
        $template = parent::createTemplate();

        $componentName = strtr($this->getReflection()->getName(), ["Component" => ""]);

        $template->setFile(
            dirname(__FILE__) . "/" .
            $componentName . "/" .
            ExtraString::lowerFirst($componentName) . ".latte"
        );
        $template->setTranslator($this->translator);
        $template->getLatte()->addFilter('i18n', '\App\Model\Translator\GettextTranslator::i18nHelper');

        return InterlosTemplate::loadTemplate($template);
    }

    protected function getPath(): string {
        $componentName = strtr($this->getReflection()->getName(), ["Component" => ""]);
        return dirname(__FILE__) . "/" . $componentName . "/";
    }

    protected function beforeRender(): void {
    }

    protected function createComponentFlashMessages(): FlashMessagesComponent {
        return new FlashMessagesComponent($this->getContext());
    }

    protected function startUp(): void {
    }
}
