<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelPeriod;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServicePeriod;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\BadRequestException;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class AddTaskCard extends SingleFormCard {

    private ServicePeriod $servicePeriod;

    public ServiceGroup $serviceGroup;

    public function injectServicePeriod(ServicePeriod $servicePeriod, ServiceGroup $serviceGroup): void {
        $this->servicePeriod = $servicePeriod;
        $this->serviceGroup = $serviceGroup;
    }

    public function checkRequirements(?array $values = null): void {
        parent::checkRequirements();
        if (!count($this->getActiveLines())) {
            throw new BadRequestException();// TODO
        }
    }

    public function decorateFormContainer(Container $container, string $lang): void {
        $items = [];
        foreach ($this->getActiveLines() as $key => $line) {
            $items[$key] = _($line->text);
        }

        $container->addSelect('group', _('Line'), $items);
    }

    protected function innerHandle(Logger $logger, array $values): void {
        // TODO: Implement innerHandle() method.
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_ADD_TASK;
    }

    public function getTitle(): string {
        return _('Add task');
    }

    public function getDescription(): Html {
        // TODO: Implement getDescription() method.
        return Html::el('span')->addText('TODO');
    }

    /**
     * @return ModelGroup[]
     */
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
