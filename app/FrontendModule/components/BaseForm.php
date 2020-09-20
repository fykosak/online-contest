<?php

use Nette\Application\UI;
use App\Model\Interlos;

class BaseForm extends UI\Form {

    public function __construct() {
        parent::__construct();
        $this->setTranslator(Interlos::getTranslator());
        $this->setRenderer($this->createRenderer());
    }

    private function createRenderer() {
        $this->getElementPrototype()->class = 'form-horizontal';
        $renderer = new Nette\Forms\Rendering\DefaultFormRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'div class="form-group"';
        $renderer->wrappers['label']['container'] = '';
        $renderer->wrappers['control']['container'] = 'div class="col-md-5"';
        $renderer->wrappers['control']['.submit'] = 'btn btn-default';
        return $renderer;
    }

    public function addSelect($name, $label = null, array $items = null, $size = null) {
        $result = parent::addSelect($name, $label, $items, $size);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }

    public function addText($name, $label = null, $cols = null, $maxLength = null) {
        $result = parent::addText($name, $label, $cols, $maxLength);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }

    public function addTextArea($name, $label = null, $cols = 40, $rows = 10) {
        $result = parent::addTextArea($name, $label, $cols, $rows);
        $result->getControlPrototype()->class = 'form-control';
        if (!$label) {
            $result->getLabelPrototype()->class = 'col-md-0 control-label';
        } else {
            $result->getLabelPrototype()->class = 'col-md-2 control-label';
        }

        return $result;
    }

    public function addPassword($name, $label = null, $cols = null, $maxLength = null) {
        $result = parent::addPassword($name, $label, $cols, $maxLength);
        $result->getControlPrototype()->class = 'form-control';
        $result->getLabelPrototype()->class = 'col-md-2 control-label';
        return $result;
    }

}
