<?php

namespace FOL\Modules\FrontendModule\Components;

use FOL\i18n\GettextTranslator;
use FOL\Modules\FrontendModule\Components\FlashMessages\FlashMessagesComponent;
use FOL\tools\InterlosTemplate;
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

    protected function createTemplate(): Template {
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
