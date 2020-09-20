<?php

use App\Model\Interlos;

class NotificationMessagesComponent extends BaseComponent {
    public function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = Interlos::getCurrentYear()->game_end->getTimestamp();
    }
}
