<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
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
    public function createAll(ModelTeam $team): array {
        $cardTypes = [
            ModelCardUsage::TYPE_SKIP,
            ModelCardUsage::TYPE_RESET,
            ModelCardUsage::TYPE_DOUBLE_POINTS,
            ModelCardUsage::TYPE_ADD_TASK,
            ModelCardUsage::TYPE_HINT,
            ModelCardUsage::TYPE_OPTIONS,
        ];
        $cards = [];
        foreach ($cardTypes as $card) {
            $cards[] = $this->create($team, $card);
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
            case ModelCardUsage::TYPE_SKIP:
                $card = new SkipCard($team);
                break;
            case ModelCardUsage::TYPE_RESET:
                $card = new ResetCard($team);
                break;
            case ModelCardUsage::TYPE_DOUBLE_POINTS:
                $card = new DoublePointsCard($team);
                break;
            case ModelCardUsage::TYPE_ADD_TASK:
                $card = new AddTaskCard($team);
                break;
            case ModelCardUsage::TYPE_HINT:
                $card = new HintCard($team);
                break;
            case ModelCardUsage::TYPE_OPTIONS:
                $card = new OptionsCard($team);
                break;
            default:
                throw new BadRequestException();
        }

        $this->container->callInjects($card);
        return $card;
    }
}
