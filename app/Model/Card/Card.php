<?php

namespace FOL\Model\Card;

use Dibi\Row;
use FOL\Model\ORM\ModelCardUsage;
use FOL\Model\ORM\ServiceCardUsage;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Explorer;
use Nette\SmartObject;
use Nette\Utils\Html;
use Throwable;

abstract class Card {

    use SmartObject;

    protected Explorer $explorer;
    protected ServiceCardUsage $serviceCardUsage;

    public function injectBase(Explorer $explorer, ServiceCardUsage $serviceCardUsage): void {
        $this->explorer = $explorer;
        $this->serviceCardUsage = $serviceCardUsage;
    }

    public final function isUsed(Row $team): bool {
        return (bool)$this->serviceCardUsage->findByTypeAndTeam($team, $this->getType());
    }

    public final function getUsage(Row $team): ?ModelCardUsage {
        return $this->serviceCardUsage->findByTypeAndTeam($team, $this->getType());
    }

    public final function logUsage(Row $team, array $values): void {
        $this->serviceCardUsage->createNewModel([
            'team_id' => $team->id_team,
            'card_type' => $this->getType(),
            'data' => $this->serializeData($values),
        ]);
    }

    protected function serializeData(array $values): string {
        return serialize($values);
    }

    /**
     * @param Row $team
     * @param Logger $logger
     * @param array $values
     * @throws Throwable
     */
    final public function handle(Row $team, Logger $logger, array $values): void {
        $this->explorer->beginTransaction();
        try {
            if (!$this->isAvailable($team)) {
                throw new ForbiddenRequestException(_('Card can not be used at this time'));
            }
            $this->innerHandle($team, $logger, $values);
            $this->logUsage($team, $values);
            $this->explorer->commit();
        } catch (Throwable$exception) {
            $this->explorer->rollBack();
            throw $exception;
        }
    }

    final public function isAvailable(Row $team): bool {
        return !$this->isUsed($team) && $this->isInnerAvailable($team);
    }

    abstract protected function isInnerAvailable(Row $team): bool;

    abstract public function decorateForm(Form $form, Row $team, string $lang): void;

    abstract protected function innerHandle(Row $team, Logger $logger, array $values): void;

    abstract public function getType(): string;

    abstract public function getTitle(): string;

    abstract public function getDescription(): Html;

}
