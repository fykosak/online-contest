<?php

namespace FOL\Modules\FrontendModule\Components\FlashMessages;
use FOL\Modules\FrontendModule\Components\BaseComponent;

class FlashMessagesComponent extends BaseComponent {
    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'flashMessages.latte');
        parent::render();
    }
}
