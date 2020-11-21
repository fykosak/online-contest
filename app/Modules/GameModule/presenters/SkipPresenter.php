<?php

namespace FOL\Modules\GameModule\Presenters;

use Nette\Application\AbortException;

class SkipPresenter extends BasePresenter {

    /**
     * @return void
     * @throws AbortException
     */
    public function renderDefault(): void {
        if (!$this->user->isAllowed('task', 'skip')) {
            $this->flashMessage(_('Již jste vyčerpali svůj limit pro počet přeskočených úloh.'), 'danger');
            $this->redirect('Task:list');
        }

        $this->setPageTitle(_('Přeskočit úkol'));
    }

    protected function createComponentSkipForm(): \SkipFormComponent {
        return new \SkipFormComponent($this->getContext());
    }
}
