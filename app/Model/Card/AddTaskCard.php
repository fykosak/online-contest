<?php

namespace FOL\Model\Card;

use FOL\Model\ORM\Models\ModelCardUsage;
use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelPeriod;
use FOL\Model\ORM\Services\ServiceGroup;
use FOL\Model\ORM\Services\ServicePeriod;
use FOL\Model\ORM\TasksService;
use Fykosak\Utils\Logging\Logger;
use Nette\Application\BadRequestException;
use Nette\Forms\Container;
use Nette\Utils\Html;

final class AddTaskCard extends SingleFormCard {

    private ServicePeriod $servicePeriod;

    public ServiceGroup $serviceGroup;

    public function injectServicePeriod(ServicePeriod $servicePeriod, ServiceGroup $serviceGroup, TasksService $tasksService): void {
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
        /** @var ModelGroup|null $group */
        $group = $this->serviceGroup->findByPrimary($values['group']);
        $this->tasksService->updateSingleCounter($this->team, $group);
    }

    public function getType(): string {
        return ModelCardUsage::TYPE_ADD_TASK;
    }

    public function getTitle(): string {
        return _('Add one problem from a series');
    }

    public function getDescription(): Html {
        return Html::el('span')->addText('the next problem from a chosen series is revealed, the team can therefore solve more problems from the series at once.');
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
