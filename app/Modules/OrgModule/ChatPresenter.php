<?php

namespace FOL\Modules\OrgModule;

use Dibi\Exception;
use FOL\Model\ORM\ChatService;
use FOL\Modules\FrontendModule\Components\ChatList\ChatListComponent;

class ChatPresenter extends BasePresenter {

    protected ChatService $chatService;

    public function injectSecondary(ChatService $chatService): void {
        $this->chatService = $chatService;
    }

    /**
     * @return void
     * @throws Exception
     */
    public function renderDefault(): void {
        $this->getComponent("chat")->setSource($this->chatService->findAll($this->lang));
        $this->setPageTitle(_("Diskuse (česká verze)"));
    }

    protected function createComponentChat(): ChatListComponent {
        return new ChatListComponent($this->getContext());
    }
}
