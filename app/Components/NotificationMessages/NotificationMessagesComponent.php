<?php

namespace FOL\Components\NotificationMessages;

use FOL\Model\ORM\Services\ServiceNotification;
use FOL\Model\ORM\Services\ServiceYear;
use FOL\Components\BaseComponent;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\NotSupportedException;

class NotificationMessagesComponent extends BaseComponent {

    private ServiceYear $serviceYear;
    private IRequest $httpRequest;
    private ServiceNotification $serviceNotification;

    private string $lang;

    public function __construct(Container $container, string $lang) {
        parent::__construct($container);
        $this->lang = $lang;
    }

    public function injectServiceYear(ServiceYear $serviceYear, IRequest $httpRequest, ServiceNotification $serviceNotification): void {
        $this->serviceYear = $serviceYear;
        $this->serviceNotification = $serviceNotification;
        $this->httpRequest = $httpRequest;
    }

    protected function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = $this->serviceYear->getCurrent()->game_end->getTimestamp();
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'notificationMessages.latte');
        parent::render();
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function handleAjax(): void {
        if (!$this->httpRequest->isAjax()) {
            throw new NotSupportedException;
        }

        $lang = $this->lang;
        $lastAsked = (int)$this->httpRequest->getQuery('lastAsked');
        $pollInterval = $this->getContext()->parameters['notifications']['pollInterval'];
        $now = time();

        if ($lastAsked == null) {
            $notifications = [];
        } else {
            $notifications = $this->serviceNotification->getNew($lastAsked, $lang);
        }

        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'notificationMessages.latte');
        $this->template->notifications = $notifications;
        $this->template->gameEnd = $this->serviceYear->getCurrent()->game_end->getTimestamp();

        $payload = [
            'html' => (string)$this->template,
            'lastAsked' => $now,
            'pollInterval' => $pollInterval,
        ];
        $this->getPresenter()->sendResponse(new JsonResponse($payload));
    }
}
