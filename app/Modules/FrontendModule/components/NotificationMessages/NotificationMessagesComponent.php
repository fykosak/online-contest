<?php

use FOL\Model\ORM\YearsService;

class NotificationMessagesComponent extends BaseComponent {

    protected YearsService $yearsService;

    public function injectYearsService(YearsService $yearsService): void {
        $this->yearsService = $yearsService;
    }

    /**
     * @return void
     * @throws \Dibi\Exception
     */
    public function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = $this->yearsService->findCurrent()->game_end->getTimestamp();
    }
}
