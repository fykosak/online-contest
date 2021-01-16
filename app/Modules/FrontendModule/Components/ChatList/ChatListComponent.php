<?php

namespace FOL\Modules\FrontendModule\Components\ChatList;

use Dibi\Exception;
use FOL\Model\ORM\ChatService;
use FOL\Modules\FrontendModule\Components\BaseForm;
use FOL\Modules\FrontendModule\Components\VisualPaginator\VisualPaginatorComponent;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Dibi\DriverException;
use FOL\Modules\FrontendModule\Components\BaseListComponent;
use Nette\ComponentModel\IComponent;

class ChatListComponent extends BaseListComponent {

    protected ChatService $chatService;

    public function injectChatService(ChatService $chatService): void {
        $this->chatService = $chatService;
    }

    /**
     * @param Form $form
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    private function handleChatSuccess(Form $form): void {
        $user = $this->getPresenter()->user;
        if (!$user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $values = $form->getValues();

        if ($values["parent_id"] == '0') {
            $values["parent_id"] = null;
        }

        // Insert a chat post
        try {
            if ($user->isInRole('org')) {
                $team = null;
                $org = 1;
            } else {
                $team = $user->getIdentity()->id_team;
                $org = 0;
            }
            $this->chatService->insert(
                $team,
                $org,
                $values["content"],
                $values["parent_id"],
                $this->getPresenter()->lang
            );
            $this->getPresenter()->flashMessage(_("Příspěvek byl vložen."), "success");
            $this->getPresenter()->redirect("this");
        } catch (DriverException $e) {
            $this->flashMessage(_("Chyba při práci s databází."), "danger");
            error_log($e->getTraceAsString());
        }
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function beforeRender(): void {
        // Paginator
        $paginator = $this->getPaginator();
        $lang = $this->getPresenter()->lang;
        //$this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
        $rootPosts = $this->chatService->findAllRoot($lang)->orderBy('inserted', 'DESC')
            ->applyLimit($paginator->itemsPerPage, $paginator->offset)
            ->fetchAll();

        $posts = [];
        foreach ($rootPosts as $rootPost) {
            $rootPost['root'] = true;
            $posts[] = $rootPost;

            $descendants = $this->chatService->findDescendants($rootPost['id_chat'], $lang)->orderBy('inserted')->fetchAll();
            foreach ($descendants as $descendant) {
                $descendant['root'] = false;
                $posts[] = $descendant;
            }
        }
        // Load template
        $this->getTemplate()->posts = $posts; //$this->getSource()->fetchAll();
    }

    /** Override paginator to count only root posts
     * @throws Exception
     */
    protected function createComponentPaginator(): VisualPaginatorComponent {
        $paginator = new VisualPaginatorComponent($this->getContext());
        $paginator->getPaginator()->itemsPerPage = $this->getLimit();
        $paginator->getPaginator()->itemCount = $this->chatService->findAllRoot($this->getPresenter()->lang)->count();
        return $paginator;
    }

    /* Hack for creating multiple ChatForms */
    protected function createComponent($name): IComponent {
        if (preg_match('/^chatForm(\d+)/', $name, $matches)) {
            $id = $matches[1];
            return $this->createInstanceChatForm($id);
        } else {
            return parent::createComponent($name);
        }
    }

    protected function createInstanceChatForm($id): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addTextArea("content")
            ->addRule(Form::FILLED, "Obsah příspěvku není vyplněn.");
        $form->addHidden("parent_id", $id);

        $form->addSubmit("chatSubmit", "Přidat příspěvek");
        $form->onSuccess[] = function (Form $form) {
            $this->handleChatSuccess($form);
        };

        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chatList.latte');
        parent::render();
    }
}
