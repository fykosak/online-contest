<?php

namespace FOL\Modules\GameModule\Presenters;

use Dibi\Exception;
use Nette\Application\AbortException;

/**
 * only for correct routing
 */
class DefaultPresenter extends BasePresenter {
    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     */
    protected function startUp(): void {
        parent::startUp();
        $this->redirect(':Game:Task:default');
    }
}