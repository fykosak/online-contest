<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;

/**
 * @property-read int id_period
 * @property-read int id_group
 * @property-read DateTimeInterface begin
 * @property-read DateTimeInterface end
 * @property-read bool allow_skip
 * @property-read bool has_bonus
 * @property-read int time_penalty
 * @property-read int reserve_size
 */
class ModelPeriod extends AbstractModel {

}
