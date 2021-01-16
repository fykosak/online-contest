<?php

namespace FOL\Modules\FrontendModule\Components\NotificationMessages;

use Dibi\Exception;
use FOL\Model\ORM\YearsService;
use FOL\Modules\FrontendModule\Components\BaseComponent;

class NotificationMessagesComponent extends BaseComponent {

    protected YearsService $yearsService;

    public function injectYearsService(YearsService $yearsService): void {
        $this->yearsService = $yearsService;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = $this->yearsService->findCurrent()->game_end->getTimestamp();
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'notificationMessages.latte');
        parent::render();
    }
}
