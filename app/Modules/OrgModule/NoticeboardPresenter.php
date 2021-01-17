<?php

namespace FOL\Modules\OrgModule;

use FOL\Model\ORM\NotificationService;
use FOL\Components\NotificationForm\NotificationFormComponent;
use Nette\Application\BadRequestException;
use Nette\Http\Response;

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
        $this->setPageTitle(_('Přidat notifikaci'));
    }

    protected function createComponentNotificationForm(): NotificationFormComponent {
        return new NotificationFormComponent($this->getContext());
    }
}
