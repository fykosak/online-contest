<?php

namespace FOL\Components\CardUsage;

use FOL\Components\BaseComponent;
use FOL\Model\Card\Card;
use FOL\Model\ORM\Services\ServiceTask;
use Nette\DI\Container;

final class CardUsageComponent extends BaseComponent {

    private string $lang;
    private Card $card;
    private ServiceTask $serviceTask;

    public function __construct(Container $container, Card $card, ServiceTask $serviceTask, string $lang) {
        parent::__construct($container);
        $this->card = $card;
        $this->serviceTask = $serviceTask;
        $this->lang = $lang;
    }

    public function render(): void {
        $this->template->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.' . $this->card->getType() . '.latte');
        $this->template->card = $this->card;
        $this->template->serviceTask = $this->serviceTask;
        $this->template->lang = $this->lang;
        parent::render();
    }
}
