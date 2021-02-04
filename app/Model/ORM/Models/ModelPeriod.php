<?php

namespace FOL\Model\ORM\Models;

use DateTime;
use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_period
 * @property-read int id_group
 * @property-read ActiveRow group
 * @property-read DateTimeInterface begin
 * @property-read DateTimeInterface end
 * @property-read bool allow_skip
 * @property-read bool has_bonus
 * @property-read int time_penalty
 * @property-read int reserve_size
 */
final class ModelPeriod extends AbstractModel {

    public function isActive(): bool {
        return $this->begin <= new DateTime() && $this->end > new DateTime();
    }

    public function getGroup(): ModelGroup {
        return ModelGroup::createFromActiveRow($this->group);
    }
}
