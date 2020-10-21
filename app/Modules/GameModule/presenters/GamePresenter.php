<?php

namespace FOL\Modules\GameModule\Presenters;

use Dibi\Exception;
use Nette\Application\AbortException;

class GamePresenter extends BasePresenter {

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    protected function startUp(): void {
        parent::startUp();
        switch ($this->getAction()) {
            case 'answer':
                $this->redirect(':Game:Answer:default');
            case 'skip':
                $this->redirect(':Game:Skip:default');
            case 'default':
                $this->redirect(':Game:Task:default');
            case 'history':
                $this->redirect(':Game:Answer:history');
        }
    }
}
