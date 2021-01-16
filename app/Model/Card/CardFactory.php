<?php

namespace FOL\Model\Card;

use Nette\Application\BadRequestException;
use Nette\DI\Container;

final class CardFactory {

    private Container $container;
    /** @var Card[] */
    private array $cards = [];

    /**
     * CardFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
        $this->create();
    }

    /**
     * @return Card[]
     */
    public function getAll(): array {
        return $this->cards;
    }

    /**
     * @param string $type
     * @return Card
     * @throws BadRequestException
     */
    public function getByType(string $type): Card {
        if (isset($this->cards[$type])) {
            return $this->cards[$type];
        }
        throw new BadRequestException();
    }

    private function create(): void {
        $this->cards = [
            'skip' => new SkipCard(),
            'reset' => new ResetCard(),
            'double_points' => new DoublePointsCard(),
            'add_task' => new AddTaskCard(),
            'hint' => new HintCard(),
            'options' => new OptionsCard(),
        ];
        foreach ($this->cards as $card) {
            $this->container->callInjects($card);
        }
    }
}
