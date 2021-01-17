<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelTeam;
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

    /**
     * @param ModelTeam $team
     * @return Card[]
     */
    public function createForTeam(ModelTeam $team): array {
        $cards = [
            'skip' => new SkipCard($team),
            'reset' => new ResetCard($team),
            'double_points' => new DoublePointsCard($team),
            'add_task' => new AddTaskCard($team),
            'hint' => new HintCard($team),
            'options' => new OptionsCard($team),
        ];
        foreach ($cards as $card) {
            $this->container->callInjects($card);
        }
        return $cards;
    }
}
