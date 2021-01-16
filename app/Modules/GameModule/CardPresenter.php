<?php

namespace FOL\Modules\GameModule;

use FOL\Components\CardForm\CardFormComponent;
use FOL\Model\Card\Card;
use FOL\Model\Card\CardFactory;
use Nette\Application\BadRequestException;

class CardPresenter extends BasePresenter {

    /** @persistent */
    public ?string $id = null;

    private CardFactory $cardFactory;

    private Card $card;

    public function injectCardFactory(CardFactory $cardFactory): void {
        $this->cardFactory = $cardFactory;
    }

    public function renderList(): void {
        $this->setPageTitle(_('Cards'));
        $this->template->cards = $this->cardFactory->getAll();
        $this->template->team = $this->getLoggedTeam();
    }

    public function renderUse(): void {
        $this->setPageTitle($this->getCard()->getTitle());
        $this->template->card = $this->getCard();
        $this->template->usage = $this->getCard()->getUsage($this->getLoggedTeam());
    }

    /**
     * @return Card
     * @throws BadRequestException
     */
    protected function getCard(): Card {
        if (!isset($this->card)) {
            $this->card = $this->cardFactory->getByType($this->id);
        }
        return $this->card;
    }

    protected function createComponentCardForm(): CardFormComponent {
        return new CardFormComponent($this->getContext(), $this->getCard(), $this->getLoggedTeam(), $this->lang);
    }
}
