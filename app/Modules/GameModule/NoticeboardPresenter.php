<?php

namespace FOL\Modules\GameModule;

use FOL\Model\ORM\Services\ServiceNotification;

class NoticeboardPresenter extends BasePresenter {

    protected ServiceNotification $serviceNotification;

    public function injectSecondary(ServiceNotification $serviceNotification): void {
        $this->serviceNotification = $serviceNotification;
    }

    public function renderDefault(): void {
        $this->template->notifications = $this->serviceNotification->getActive($this->lang);
        $this->setPageTitle(_('Důležitá oznámení'));
    }
}
