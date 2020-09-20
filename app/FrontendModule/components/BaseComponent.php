<?php

use App\Model\Interlos;
use App\Tools\InterlosTemplate;
use Nette\Application\UI\ITemplate;
use Nette\Bridges\ApplicationLatte\Template;

abstract class BaseComponent extends Nette\Application\UI\Control {
    public function __construct() {
        parent::__construct();
        $this->startUp();
    }

    public function render(): void {
        $this->beforeRender();
        $this->getTemplate()->render();
    }

    protected function createTemplate(): ITemplate {
        /** @var Template $template */
        $template = parent::createTemplate();

        $componentName = strtr($this->getReflection()->getName(), ["Component" => ""]);

        $template->setFile(
            dirname(__FILE__) . "/" .
            $componentName . "/" .
            ExtraString::lowerFirst($componentName) . ".latte"
        );
        $template->setTranslator(Interlos::getTranslator());
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
        return new FlashMessagesComponent();
    }

    protected function startUp(): void {
    }
}
