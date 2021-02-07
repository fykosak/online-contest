<?php

namespace FOL\Modules\OrgModule;

use FOL\Components\NotificationForm\NotificationFormComponent;

class NoticeboardPresenter extends BasePresenter {

    public function renderAdd(): void {
        $this->setPageTitle(_('Přidat notifikaci'));
    }

    protected function createComponentNotificationForm(): NotificationFormComponent {
        return new NotificationFormComponent($this->getContext());
    }
}
