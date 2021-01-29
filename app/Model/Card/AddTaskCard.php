<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelPeriod;
use FOL\Model\ORM\Services\ServicePeriod;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\BadRequestException;
use Nette\Forms\Container;
use Nette\Utils\Html;

class AddTaskCard extends Card {

    private ServicePeriod $servicePeriod;

    public function injectServicePeriod(ServicePeriod $servicePeriod): void {
        $this->servicePeriod = $servicePeriod;
    }

    public function checkRequirements(?array $values = null): void {
        parent::checkRequirements();
        if (!count($this->getActiveLines())) {
            throw new BadRequestException();// TODO
        }
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        // TODO: Implement decorateForm() method.
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return 'add_task';
    }

    public function getTitle(): string {
        return _('Add task');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    private function getActiveLines(): array {
        $periods = $this->servicePeriod->getTable();
        $groups = [];
        /** @var ModelPeriod $period */
        foreach ($periods as $period) {
            if ($period->isActive()) {
                $group = $period->getGroup();
                $groups[$group->id_group] = $group;
            }
        }
        return $groups;
    }
}
