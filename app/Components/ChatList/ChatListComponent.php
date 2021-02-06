<?php

namespace FOL\Components\ChatList;

use DateTime;
use FOL\Components\BaseForm;
use FOL\Components\VisualPaginator\VisualPaginatorComponent;
use FOL\Model\ORM\Models\ModelChat;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Services\ServiceChat;
use Fykosak\Utils\ORM\Exceptions\ModelException;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use FOL\Components\BaseListComponent;
use Nette\ComponentModel\IComponent;
use Nette\Database\Table\Selection;
use Nette\DI\Container;

final class ChatListComponent extends BaseListComponent {

    private ServiceChat $serviceChat;
    private ?ModelTeam $team;
    private string $lang;

    public function __construct(Container $container, ?ModelTeam $team, string $lang) {
        parent::__construct($container);
        $this->team = $team;
        $this->lang = $lang;
    }

    public function injectChatService(ServiceChat $serviceChat): void {
        $this->serviceChat = $serviceChat;
    }

    /**
     * @param Form $form
     * @return void
     * @throws AbortException
     */
    private function handleChatSuccess(Form $form): void {
        $user = $this->getPresenter()->user;
        if (!$user->isLoggedIn()) {
            $this->redirect('Sign:in');
        }

        $values = $form->getValues();

        if ($values['parent_id'] == '0') {
            $values['parent_id'] = null;
        }

        // Insert a chat post
        try {
            $this->serviceChat->createNewModel([
                'id_parent' => $values['parent_id'],
                'id_team' => isset($this->team) ? $this->team->id_team : null,
                'org' => isset($this->team) ? 0 : 1,
                'content' => $values['content'],
                'lang' => $this->getPresenter()->lang,
                'inserted' => new DateTime(),
            ]);
            $this->serviceLog->log(isset($this->team) ? $this->team->id_team : null, 'chat_inserted', 'The team successfully contributed to the chat.');

            $this->getPresenter()->flashMessage(_('Příspěvek byl vložen.'), 'success');
            $this->getPresenter()->redirect('this');
        } catch (ModelException $e) {
            $this->flashMessage(_('Chyba při práci s databází.'), 'danger');
            error_log($e->getTraceAsString());
        }
    }

    protected function beforeRender(): void {
        // Paginator
        $paginator = $this->getPaginator();
        $lang = $this->getPresenter()->lang;
        //$this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
        $rootPosts = $this->serviceChat->getAllRoot($lang)
            ->order('inserted DESC')
            ->limit($paginator->itemsPerPage, $paginator->offset);

        /** @var ModelChat[] $rootPosts */
        $posts = [];
        foreach ($rootPosts as $rootPost) {
            $posts[] = $rootPost;
            $descendants = $this->serviceChat->getDescendants($rootPost->id_chat, $lang)->order('inserted');
            foreach ($descendants as $descendant) {
                $posts[] = $descendant;
            }
        }
        // Load template
        $this->template->posts = $posts; //$this->getSource()->fetchAll();
    }

    /** Override paginator to count only root posts
     * @return VisualPaginatorComponent
     */
    protected function createComponentPaginator(): VisualPaginatorComponent {
        $paginator = new VisualPaginatorComponent($this->getContext());
        $paginator->getPaginator()->itemsPerPage = $this->getLimit();
        $paginator->getPaginator()->itemCount = $this->serviceChat->getAllRoot($this->getPresenter()->lang)->count();
        return $paginator;
    }

    protected function createComponent(string $name): IComponent {
        if (preg_match('/^chatForm(\d+)/', $name, $matches)) {
            $id = $matches[1];
            return $this->createInstanceChatForm($id);
        } else {
            return parent::createComponent($name);
        }
    }

    /**
     * @param mixed $id
     * @return BaseForm
     */
    protected function createInstanceChatForm($id): BaseForm {
        $form = new BaseForm($this->getContext());

        $form->addTextArea('content')
            ->addRule(Form::FILLED, 'Obsah příspěvku není vyplněn.');
        $form->addHidden('parent_id', $id);

        $form->addSubmit('chatSubmit', 'Přidat příspěvek');
        $form->onSuccess[] = function (Form $form) {
            $this->handleChatSuccess($form);
        };

        return $form;
    }

    protected function getSource(): ?Selection {
        return $this->serviceChat->getAll($this->lang);
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'chatList.latte');
        parent::render();
    }
}
