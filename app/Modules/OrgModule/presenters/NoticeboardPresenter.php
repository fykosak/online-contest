<?php

namespace FOL\Modules\OrgModule\Presenters;

use FOL\Model\ORM\NotificationService;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Http\Response;
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
            throw new ForbiddenRequestException('Nemáte oprávnění pro přidání notifikace.', Response::S403_FORBIDDEN);
        }
        $this->setPageTitle(_("Přidat notifikaci"));
    }


    protected function createComponentNotificationForm(): NotificationFormComponent {
        return new NotificationFormComponent($this->getContext());
    }
}
