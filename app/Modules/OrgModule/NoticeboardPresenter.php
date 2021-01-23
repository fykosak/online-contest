<?php

namespace FOL\Modules\OrgModule;

use FOL\Components\NotificationForm\NotificationFormComponent;
use Nette\Application\BadRequestException;
use Nette\Http\Response;

class NoticeboardPresenter extends BasePresenter {

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
