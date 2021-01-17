<?php

namespace FOL\Components;

use Nette\Application\UI\Form;
use Nette\DI\Container;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\TextArea;
use Nette\Forms\Controls\TextInput;
use Nette\Forms\IFormRenderer;
use Nette\Forms\Rendering\DefaultFormRenderer;
use Nette\Localization\Translator;

class BaseForm extends Form {

    protected Translator $translator;

    public function __construct(Container $container) {
        parent::__construct();
        $container->callInjects($this);
        $this->setTranslator($this->translator);
        $this->setRenderer($this->createRenderer());
    }

    public function injectTranslator(Translator $translator): void {
        $this->translator = $translator;
    }

    private function createRenderer(): IFormRenderer {
        $this->getElementPrototype()->class = 'form-horizontal';
        $renderer = new DefaultFormRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'div class="form-group"';
        $renderer->wrappers['label']['container'] = '';
        $renderer->wrappers['control']['container'] = 'div class="col-md-5"';
        $renderer->wrappers['control']['.submit'] = 'btn btn-primary';
        return $renderer;
    }

    public function addSelect($name, $label = null, array $items = null, $size = null): SelectBox {
        $result = parent::addSelect($name, $label, $items, $size);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }

    public function addText($name, $label = null, $cols = null, $maxLength = null): TextInput {
        $result = parent::addText($name, $label, $cols, $maxLength);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }

    public function addTextArea($name, $label = null, $cols = 40, $rows = 10): TextArea {
        $result = parent::addTextArea($name, $label, $cols, $rows);
        $result->getControlPrototype()->class = 'form-control';
        if (!$label) {
            $result->getLabelPrototype()->class = 'col-md-0 control-label';
        } else {
            $result->getLabelPrototype()->class = 'col-md-2 control-label';
        }

        return $result;
    }

    public function addPassword($name, $label = null, $cols = null, $maxLength = null): TextInput {
        $result = parent::addPassword($name, $label, $cols, $maxLength);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }
}
