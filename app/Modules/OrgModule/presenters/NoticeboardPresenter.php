<?php

namespace FOL\Modules\OrgModule\Presenters;

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
     * @throws BadRequestException
     */
    public function renderAdd(): void {
        if (!$this->user->isAllowed('noticeboard', 'add')) {
            $this->error('Nemáte oprávnění pro přidání notifikace.', Response::S403_FORBIDDEN);
        }
        $this->setPageTitle(_("Přidat notifikaci"));
    }


    protected function createComponentNotificationForm(): NotificationFormComponent {
        return new NotificationFormComponent($this->getContext());
    }
}
