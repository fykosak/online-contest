<?php

namespace FOL\Modules\GameModule;

use Dibi\Exception;
use FOL\Components\CardForm\CardFormComponent;
use FOL\Components\CardUsage\CardUsageComponent;
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

    /**
     * @throws Exception
     */
    public function renderList(): void {
        $this->setPageTitle(_('Cards'));
        $this->template->cards = $this->cardFactory->createForTeam($this->getLoggedTeam2());
        $this->template->team = $this->getLoggedTeam2();
    }

    /**
     * @throws BadRequestException
     */
    public function renderUse(): void {
        $this->setPageTitle($this->getCard()->getTitle());
        $this->template->card = $this->getCard();
    }

    /**
     * @return Card
     * @throws BadRequestException
     */
    protected function getCard(): Card {
        if (!isset($this->card)) {
            $cards = $this->cardFactory->createForTeam($this->getLoggedTeam2());
            if (!isset($cards[$this->id])) {
                throw new BadRequestException();
            }
            $this->card = $cards[$this->id];
        }
        return $this->card;
    }

    /**
     * @return CardFormComponent
     * @throws BadRequestException
     * @throws Exception
     */
    protected function createComponentCardForm(): CardFormComponent {
        return new CardFormComponent($this->getContext(), $this->getCard(), $this->lang);
    }

    /**
     * @return CardUsageComponent
     * @throws BadRequestException
     * @throws Exception
     */
    protected function createComponentCardUsage(): CardUsageComponent {
        return new CardUsageComponent($this->getContext(), $this->getCard(), $this->lang);
    }
}
