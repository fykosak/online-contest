<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use Fykosak\Utils\Localization\UnsupportedLanguageException;
use Nette\Application\AbortException;

/**
 * only for correct routing
 */
class DefaultPresenter extends BasePresenter {

    /**
     * @return void
     * @throws Exception
     * @throws AbortException
     * @throws UnsupportedLanguageException
     */
    protected function startUp(): void {
        parent::startUp();
        $this->redirect(':Game:Task:default');
    }
}
