<?php

namespace FOL\Components\NotificationMessages;

use DateTime;
use Exception;
use FOL\Model\GameSetup;
use FOL\Model\ORM\Models\ModelNotification;
use FOL\Model\ORM\Services\ServiceNotification;
use FOL\Components\BaseComponent;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\DI\Container;
use Nette\Http\IRequest;
use Nette\NotSupportedException;

final class NotificationMessagesComponent extends BaseComponent {

    private IRequest $httpRequest;
    private ServiceNotification $serviceNotification;
    private GameSetup $gameSetup;

    private string $lang;

    public function __construct(Container $container, string $lang) {
        parent::__construct($container);
        $this->lang = $lang;
    }

    public function injectServiceYear(IRequest $httpRequest, ServiceNotification $serviceNotification, GameSetup $gameSetup): void {
        $this->serviceNotification = $serviceNotification;
        $this->httpRequest = $httpRequest;
        $this->gameSetup = $gameSetup;
    }

    /**
     * @throws Exception
     */
    protected function beforeRender(): void {
        parent::beforeRender();
        $this->template->gameEnd = (new DateTime($this->gameSetup->gameEnd))->getTimestamp();
    }

    public function render(): void {
        $this->template->notifications = [];
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
        $data = [];
        /** @var ModelNotification $notification */
        foreach ($notifications as $notification) {
            $data[] = $notification->__toArray();
        }

        $payload = [
            'notifications' => $data,
            'lastAsked' => $now,
            'pollInterval' => $pollInterval,
        ];
        $this->getPresenter()->sendResponse(new JsonResponse($payload));
    }
}
