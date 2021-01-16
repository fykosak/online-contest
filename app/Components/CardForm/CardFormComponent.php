<?php

namespace FOL\Components\CardForm;

use Dibi\Row;
use FOL\Components\BaseComponent;
use FOL\Model\Card\Card;
use FOL\Modules\FrontendModule\Components\BaseForm;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

class CardFormComponent extends BaseComponent {

    private Row $team;
    private string $lang;
    private Card $card;

    public function __construct(Container $container, Card $card, Row $team, string $lang) {
        parent::__construct($container);
        $this->lang = $lang;
        $this->card = $card;
        $this->team = $team;
    }

    protected function createComponentForm(): ?IComponent {
        $form = new BaseForm($this->getContext());
        $this->card->decorateForm($form, $this->team, $this->lang);
        $form->addSubmit('submit', _('Use'));
        $form->onSuccess[] = function (Form $form) {
            $logger = new MemoryLogger();
            $this->card->handle($this->team, $logger, $form->getValues('array'));
            FlashMessageDump::dump($logger, $this->getPresenter());
        };
        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        $this->getTemplate()->available = $this->card->isAvailable($this->team);
        parent::render();
    }
}
