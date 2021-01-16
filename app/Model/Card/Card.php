<?php

namespace FOL\Model\Card;

use Dibi\Row;
use FOL\Model\ORM\ServiceCardUsage;
use Fykosak\Utils\Logging\Logger;
use Nette\Database\Explorer;
use Nette\SmartObject;
use Throwable;

abstract class Card {

    public string $type;

    protected Explorer $explorer;
    protected ServiceCardUsage $serviceCardUsage;

    use SmartObject;

    public final function isUsed(Row $team): bool {
        return (bool)$this->serviceCardUsage->getTable()
            ->where('team_id', $team->id_team)
            ->where('card_type', $this->type)->fetch();
    }

    public final function logUsage(Row $team, ...$args): void {
        $this->serviceCardUsage->createNewModel([
            'team_id' => $team->id_team,
            'card_type' => $this->type,
            'data' => $this->serializeData(...$args),
        ]);
    }

    protected function serializeData(...$args): string {
        return serialize($args);
    }

    abstract protected function innerHandle(Row $team, Logger $logger, ...$args): void;

    /**
     * @param Row $team
     * @param Logger $logger
     * @param mixed ...$args
     * @throws Throwable
     */
    public function handle(Row $team, Logger $logger, ...$args): void {
        $this->explorer->beginTransaction();
        try {
            $this->innerHandle($team, $logger, ...$args);
            $this->logUsage($team, ...$args);
            $this->explorer->commit();
        } catch (Throwable$exception) {
            $this->explorer->rollBack();
            throw $exception;
        }
    }
}
