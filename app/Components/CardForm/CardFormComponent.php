<?php

namespace FOL\Components\CardForm;

use FOL\Components\BaseComponent;
use FOL\Model\Card\Exceptions\CardCannotBeUsedException;
use FOL\Components\BaseForm;
use FOL\Model\Card\SingleFormCard;
use Fykosak\Utils\Logging\FlashMessageDump;
use Fykosak\Utils\Logging\MemoryLogger;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;
use Nette\DI\Container;

class CardFormComponent extends BaseComponent {

    private string $lang;
    private SingleFormCard $card;
    protected const CONTAINER = 'options';

    public function __construct(Container $container, SingleFormCard $card, string $lang) {
        parent::__construct($container);
        $this->lang = $lang;
        $this->card = $card;
    }

    protected function createComponentForm(): ?IComponent {
        $form = new BaseForm($this->getContext());
        $container = new \Nette\Forms\Container();
        $this->card->decorateFormContainer($container, $this->lang);
        $form->addComponent($container, self::CONTAINER);
        $form->addSubmit('submit', _('Use'));
        $form->onSuccess[] = function (Form $form) {
            $logger = new MemoryLogger();
            $this->card->handle($logger, $form->getValues('array')[self::CONTAINER]);
            FlashMessageDump::dump($logger, $this->getPresenter());
            $this->getPresenter()->redirect('this');
        };
        return $form;
    }

    public function render(): void {
        $this->getTemplate()->setFile(__DIR__ . DIRECTORY_SEPARATOR . 'layout.latte');
        try {
            $this->card->checkRequirements();
            $this->template->reason = null;
        } catch (CardCannotBeUsedException $exception) {
            $this->template->reason = $exception->getMessage();
        }
        parent::render();
    }
}
