<?php

namespace FOL\Modules\GameModule;

use FOL\Model\ORM\Services\ServiceNotification;
use Nette\Application\AbortException;
use Nette\Application\Responses\JsonResponse;
use Nette\NotSupportedException;

class NoticeboardPresenter extends BasePresenter {

    protected ServiceNotification $serviceNotification;

    public function injectSecondary(ServiceNotification $serviceNotification): void {
        $this->serviceNotification = $serviceNotification;
    }

    public function renderDefault(): void {
        $this->template->notifications = $this->serviceNotification->getActive($this->lang);
        $this->setPageTitle(_('Důležitá oznámení'));
    }

    /**
     * @return void
     * @throws AbortException
     */
    public function actionAjax(): void {
        if (!$this->isAjax()) {
            throw new NotSupportedException;
        }

        $lang = $this->lang;
        $lastAsked = (int)$this->getHttpRequest()->getQuery('lastAsked');
        $pollInterval = $this->context->parameters['notifications']['pollInterval'];
        $now = time();

        if ($lastAsked == null) {
            $notifications = [];
        } else {
            $notifications = $this->serviceNotification->getNew($lastAsked, $lang);
        }

        $this->template->setFile(__DIR__ . '/templates/Noticeboard/@notificationsContainer.latte');
        $this->template->notifications = $notifications;

        $payload = [
            'html' => (string)$this->template,
            'lastAsked' => $now,
            'pollInterval' => $pollInterval,
        ];
        $this->sendResponse(new JsonResponse($payload));
    }
}
