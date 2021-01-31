<?php

namespace FOL\Modules\OrgModule;

use FOL\Components\ChatList\ChatListComponent;
use FOL\Model\ORM\Services\ServiceChat;

class ChatPresenter extends BasePresenter {

    protected ServiceChat $serviceChat;

    public function injectSecondary(ServiceChat $serviceChat): void {
        $this->serviceChat = $serviceChat;
    }


    public function renderDefault(): void {
        $this->getComponent('chat')->setSource($this->serviceChat->getAll($this->lang));
        $this->setPageTitle(_('Diskuse (česká verze)'));
    }

    protected function createComponentChat(): ChatListComponent {
        return new ChatListComponent($this->getContext(),null);
    }
}
