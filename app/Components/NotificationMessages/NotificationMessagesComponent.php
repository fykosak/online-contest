<?php

namespace FOL\Components\NotificationMessages;

use FOL\Model\ORM\Services\ServiceYear;
use FOL\Components\BaseComponent;

class NotificationMessagesComponent extends BaseComponent {

    protected ServiceYear $serviceYear;

    public function injectServiceYear(ServiceYear $serviceYear): void {
        $this->serviceYear = $serviceYear;
    }

    protected function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = $this->serviceYear->getCurrent()->game_end->getTimestamp();
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'notificationMessages.latte');
        parent::render();
    }
}
