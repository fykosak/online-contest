<?php

namespace FOL\Model\ORM\Services;

use FOL\Model\ORM\Models\ModelGroup;
use FOL\Model\ORM\Models\ModelPeriod;
use Fykosak\Utils\ORM\AbstractService;
use Nette\Database\Conventions;
use Nette\Database\Explorer;

final class ServicePeriod extends AbstractService {

    public function __construct(Explorer $connection, Conventions $conventions) {
        parent::__construct($connection, $conventions, 'period', ModelPeriod::class);
    }

    public function findCurrent(ModelGroup $group): ?ModelPeriod {
        /** @var ModelPeriod|null $period */
        $period = $this->getTable()->where('id_group', $group->id_group)->where('begin <= NOW() AND end > NOW()')->fetch();
        return $period;
    }
}
