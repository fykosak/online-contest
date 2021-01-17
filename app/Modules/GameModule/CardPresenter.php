<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use FOL\Components\CardForm\CardFormComponent;
use FOL\Model\Card\Card;
use FOL\Model\Card\CardFactory;
use Nette\Application\BadRequestException;
use Tracy\Debugger;

class CardPresenter extends BasePresenter {

    /** @persistent */
    public ?string $id = null;

    private CardFactory $cardFactory;

    private Card $card;

    public function injectCardFactory(CardFactory $cardFactory): void {
        $this->cardFactory = $cardFactory;
    }

    public function renderList(): void {
        Debugger::barDump($this->getLoggedTeam());
        $this->setPageTitle(_('Cards'));
        $this->template->cards = $this->cardFactory->createForTeam($this->getLoggedTeam());
        $this->template->team = $this->getLoggedTeam();
    }

    public function renderUse(): void {
        $this->setPageTitle($this->getCard()->getTitle());
        $this->template->card = $this->getCard();
    }

    /**
     * @return Card
     * @throws BadRequestException
     * @throws Exception
     */
    protected function getCard(): Card {
        if (!isset($this->card)) {
            $cards = $this->cardFactory->createForTeam($this->getLoggedTeam());
            if (!isset($cards[$this->id])) {
                throw new BadRequestException();
            }
            $this->card = $cards[$this->id];
        }
        return $this->card;
    }

    protected function createComponentCardForm(): CardFormComponent {
        return new CardFormComponent($this->getContext(), $this->getCard(), $this->lang);
    }
}
