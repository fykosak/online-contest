<?php

namespace App\FrontendModule\Presenters;

use App\Model\Interlos;
use Exception;
use Tracy\ILogger;
use Nette\Application\BadRequestException;


/**
 * Error presenter.
 */
class ErrorPresenter extends BasePresenter {

    private ILogger $logger;

    public function __construct(ILogger $logger, Interlos $interlos) {
        parent::__construct($interlos);
        $this->logger = $logger;
    }


    /**
     * @param Exception
     * @return void
     */
    public function renderDefault($exception): void {
        if ($exception instanceof BadRequestException) {
            $code = $exception->getCode();
            // load template 403.latte or 404.latte or ... 4xx.latte
            $this->setView(in_array($code, [403, 404, 405, 410, 500]) ? $code : '4xx');
            // log to access.log
            $this->logger->log("HTTP code $code: {$exception->getMessage()} in {$exception->getFile()}:{$exception->getLine()}", 'access');

        } else {
            $this->setView('500'); // load template 500.latte
            $this->logger->log($exception, ILogger::EXCEPTION); // and log exception
        }

        if ($this->isAjax()) { // AJAX request? Note this error in payload.
            $this->payload->error = true;
            $this->terminate();
        }
    }

}
