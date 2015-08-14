<?php

use Nette\Application\UI\Form,
    Nette\ComponentModel\IContainer,
    App\Model\Interlos,
    Nette\Http\Request;

class NotificationMessagesComponent extends BaseComponent
{
    public function beforeRender() {
        parent::beforeRender();
        $this->template->gameEnd = Interlos::getCurrentYear()->game_end->getTimestamp();
    }
}