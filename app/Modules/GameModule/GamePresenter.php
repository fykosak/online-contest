<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\AbortException;

class GamePresenter extends BasePresenter {

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    protected function startUp(): void {
        parent::startUp();
        switch ($this->getAction()) {
            case 'answer':
                $this->redirect(':Game:Answer:default');
            case 'default':
                $this->redirect(':Game:Task:default');
            case 'history':
                $this->redirect(':Game:Answer:history');
        }
    }
}
