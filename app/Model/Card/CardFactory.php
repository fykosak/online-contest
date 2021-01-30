<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelTeam;
use Nette\Application\BadRequestException;
use Nette\DI\Container;

final class CardFactory {

    private Container $container;

    /**
     * CardFactory constructor.
     * @param Container $container
     */
    public function __construct(Container $container) {
        $this->container = $container;
    }

    /**
     * @param ModelTeam $team
     * @return Card[]
     * @throws BadRequestException
     */
    public function createForTeam(ModelTeam $team): array {
        $cards = ['skip', 'reset', 'double_points', 'add_task', 'hint', 'options',];
        foreach ($cards as $card) {
            $this->create($team, $card);
        }
        return $cards;
    }

    /**
     * @param ModelTeam $team
     * @param string $type
     * @return Card
     * @throws BadRequestException
     */
    public function create(ModelTeam $team, string $type): Card {
        switch ($type) {
            case 'skip' :
                $card = new SkipCard($team);
                break;
            case 'reset' :
                $card = new ResetCard($team);
                break;
            case 'double_points'  :
                $card = new DoublePointsCard($team);
                break;
            case 'add_task'  :
                $card = new AddTaskCard($team);
                break;
            case 'hint'  :
                $card = new HintCard($team);
                break;
            case 'options'  :
                $card = new OptionsCard($team);
                break;
            default:
                throw new BadRequestException();
        }

        $this->container->callInjects($card);
        return $card;
    }
}
