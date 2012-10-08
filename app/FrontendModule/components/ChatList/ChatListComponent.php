<?php
class ChatListComponent extends BaseListComponent {

	// PUBLIC METHODS

	public function chatSubmitted(Form $form) {
		$values = $form->getValues();
		// Insert a chat post
		try {
			Interlos::chat()->insert(
					Environment::getUser()->getIdentity()->id_team,
					$values["content"]
			);
			$this->getPresenter()->flashMessage(_("Příspěvek byl vložen."), "success");
			$this->getPresenter()->redirect("this");
		}
		catch (DibiException $e) {
			$this->flashMessage(_("Chyba při práci s databází."), "error");
			error_log($e->getTraceAsString());
		}
	}

	// PROTECTED METHODS

	protected function beforeRender() {
		// Paginator
		$paginator = $this->getPaginator();
		$this->getSource()->applyLimit($paginator->itemsPerPage, $paginator->offset);
		// Load template
		$this->getTemplate()->posts = $this->getSource()->fetchAll();
	}

	protected function createComponentChatForm($name) {
		$form = new BaseForm($this, $name);

		$form->addTextArea("content")
				->addRule(Form::FILLED, "Obsah příspěvku není vyplněn.");

		$form->addSubmit("chatSubmit","Přidat příspěvek");
		$form->onSubmit[] = array($this, "chatSubmitted");

		return $form;
	}

}