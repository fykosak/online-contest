<?php

namespace App\FrontendModule\Presenters;

use Nette\Application\Responses\JsonResponse;
use App\Model\NotificationModel;
use Nette\Http\Response;
use Nette\NotSupportedException;
use NotificationFormComponent;

class NoticeboardPresenter extends BasePresenter {

    protected NotificationModel $notificationModel;

    public function injectSecondary(NotificationModel $notificationModel): void {
        $this->notificationModel = $notificationModel;
    }

    public function renderDefault(): void {
        $this->template->notifications = $this->notificationModel->findActive($this->lang);
        $this->setPageTitle(_("Důležitá oznámení"));
    }

    public function renderAdd(): void {
        if (!$this->user->isAllowed('noticeboard', 'add')) {
            $this->error('Nemáte oprávnění pro přidání notifikace.', Response::S403_FORBIDDEN);
        }
        $this->setPageTitle(_("Přidat notifikaci"));
    }

    public function actionAjax(): void {
        if (!$this->isAjax()) {
            throw new NotSupportedException;
        }

        $lang = $this->lang;
        $lastAsked = (int)$this->getHttpRequest()->getQuery('lastAsked');
        $pollInterval = $this->context->parameters['notifications']['pollInterval'];
        $now = time();

        if ($lastAsked == null) {
            $notification = [
                'message' => _('Sledujte prosím nástěnku.'),
                'created' => 0,
            ];
            $notifications[] = $notification;
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

    protected function createComponentNotificationForm(): NotificationFormComponent {
        return new NotificationFormComponent($this->notificationModel);
    }
}
