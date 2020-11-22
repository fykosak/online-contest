<?php

namespace FOL\Modules\GameModule\Presenters;

use Dibi\Exception;
use FOL\Model\ORM\NotificationService;
use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\Response;
use Nette\NotSupportedException;
use NotificationFormComponent;

class NoticeboardPresenter extends BasePresenter {

    protected NotificationService $notificationModel;

    public function injectSecondary(NotificationService $notificationModel): void {
        $this->notificationModel = $notificationModel;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderDefault(): void {
        $this->template->notifications = $this->notificationModel->findActive($this->lang);
        $this->setPageTitle(_('Důležitá oznámení'));
    }

    /**
     * @return void
     * @throws Exception
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
            $notifications = $this->notificationModel->findNew($lastAsked, $lang);
        }

        $this->template->setFile(__DIR__ . '/../templates/Noticeboard/@notificationsContainer.latte');
        $this->template->notifications = $notifications;

        $payload = [
            'html' => (string)$this->template,
            'lastAsked' => $now,
            'pollInterval' => $pollInterval,
        ];
        $this->sendResponse(new JsonResponse($payload));
    }
}
