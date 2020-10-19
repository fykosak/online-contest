<?php

class FlashMessagesComponent extends BaseComponent {
    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'flashMessages.latte');
        parent::render();
    }
}
