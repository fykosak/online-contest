<?php

//namespace App\FrontendModule\Components;

use Nette,
    App\Model\Interlos,
    App\Tools\InterlosTemplate;

abstract class BaseComponent extends Nette\Application\UI\Control {
	public function __construct(/*Nette\*/Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->startUp();
	}

	public function render() {
		$this->beforeRender();
		$this->getTemplate()->render();

	}

	protected function createTemplate() {
		$template = parent::createTemplate();

		$componentName = strtr($this->getReflection()->getName(), array("Component" => ""));

		$template->setFile(
				dirname(__FILE__) . "/" .
				$componentName . "/" .
				\ExtraString::lowerFirst($componentName) . ".latte"
		);
                $template->setTranslator(Interlos::getTranslator());
                $template->registerHelper('i18n', 'GettextTranslator::i18nHelper');

		return InterlosTemplate::loadTemplate($template);
	}

	protected function getPath() {
		$componentName = strtr($this->getReflection()->getName(), array("Component" => ""));
		return dirname(__FILE__) . "/" . $componentName . "/";
	}

	protected function beforeRender() {

	}

	protected function createComponentFlashMessages($name) {
		return new FlashMessagesComponent($this, $name);
	}

	protected function startUp() {}
}
