<?php

use App\Model\Interlos,
    Nette\Application\UI\Form;

class ChatListComponent extends BaseListComponent {

	// PUBLIC METHODS

	public function chatSubmitted(Form $form) {
                if(!$this->getPresenter()->user->isLoggedIn()){
                    $this->redirect('Sign:in');
                }
            
		$values = $form->getValues();
                
                if($values["parent_id"]=='0'){
                    $values["parent_id"] = null;
                }
                
		// Insert a chat post
		try {                    
			Interlos::chat()->insert(
                            $this->getPresenter()->user->getIdentity()->id_team,
                            $values["content"],
                            $values["parent_id"],
                            $this->getPresenter()->lang
			);
			$this->getPresenter()->flashMessage(_("Příspěvek byl vložen."), "success");
			$this->getPresenter()->redirect("this");
		}
		catch (DibiException $e) {
			$this->flashMessage(_("Chyba při práci s databází."), "danger");
			error_log($e->getTraceAsString());
		}
	}

	// PROTECTED METHODS

	protected function beforeRender() {
		// Paginator
		$paginator = $this->getPaginator();
                $lang = $this->getPresenter()->lang;
		//$this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
                $rootPosts = Interlos::chat()->findAllRoot($lang)->orderBy('inserted','DESC')
                        ->applyLimit($paginator->itemsPerPage, $paginator->offset)
                        ->fetchAll();
                
                $posts = array();
                foreach ($rootPosts as $rootPost) {
                    $rootPost['root'] = true;
                    $posts[] = $rootPost;
                    
                    $descendants = Interlos::chat()->findDescendants($rootPost['id_chat'], $lang)->orderBy('inserted')->fetchAll();
                    foreach ($descendants as $descendant){
                        $descendant['root'] = false;
                        $posts[] = $descendant;
                    }
                }
		// Load template
		$this->getTemplate()->posts = $posts; //$this->getSource()->fetchAll();
	}
        
        /** Override paginator to count only root posts */
	protected function createComponentPaginator($name) {
		$paginator = new VisualPaginatorComponent($this, $name);
		$paginator->paginator->itemsPerPage = $this->getLimit();
		$paginator->paginator->itemCount = Interlos::chat()->findAllRoot($this->getPresenter()->lang)->count();
		return $paginator;
	}
        
        /* Hack for creating multiple ChatForms */
        protected function createComponent($name) {
            if(preg_match('/^chatForm(\d+)/', $name, $matches)){
                $id = $matches[1];
                return $this->createInstanceChatForm($name, $id);
            }
            else{
                return parent::createComponent($name);
            }
        }
        
        protected function createInstanceChatForm($name, $id){
                $form = new BaseForm($this, $name);

		$form->addTextArea("content")
				->addRule(Form::FILLED, "Obsah příspěvku není vyplněn.");
                $form->addHidden("parent_id", $id);

		$form->addSubmit("chatSubmit","Přidat příspěvek");
		$form->onSubmit[] = array($this, "chatSubmitted");

		return $form;
        }

//	protected function createComponentChatForm($name) {
//            //throw new Exception;
//		$form = new BaseForm($this, $name);
//
//		$form->addTextArea("content")
//				->addRule(Form::FILLED, "Obsah příspěvku není vyplněn.");
//
//		$form->addSubmit("chatSubmit","Přidat příspěvek");
//		$form->onSubmit[] = array($this, "chatSubmitted");
//
//		return $form;
//	}

}