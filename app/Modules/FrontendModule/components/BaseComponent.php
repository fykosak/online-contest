<?php

use App\Model\Translator\GettextTranslator;
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

    public function injectTranslator(ITranslator $translator): void {
        $this->translator = $translator;
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    /**
     * @return ITemplate
     * @throws DataNotFoundException
     */
    protected function createTemplate(): ITemplate {
        /** @var Template $template */
        $template = parent::createTemplate();
        $template->setTranslator($this->translator);
        $template->getLatte()->addFilter('i18n', function (...$args) {
            return GettextTranslator::i18nHelper(...$args);
        });
        return InterlosTemplate::loadTemplate($template);
    }

    protected function beforeRender(): void {
    }

    protected function createComponentFlashMessages(): FlashMessagesComponent {
        return new FlashMessagesComponent($this->getContext());
    }

    protected function startUp(): void {
    }
}
