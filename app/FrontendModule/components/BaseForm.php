<?php
class BaseForm extends AppForm
{
    public function __construct(IComponentContainer $parent = NULL, $name = NULL) {
        parent::__construct($parent, $name);
        $this->setTranslator(Interlos::getTranslator());
    }
}
