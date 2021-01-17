<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;

/**
 * @property-read int id_group
 * @property-read int id_year
 * @property-read DateTimeInterface to_show
 * @property-read string type
 * @property-read string code_name
 * @property-read string text
 * @property-read bool allow_zeroes
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 */
class ModelGroup extends AbstractModel {

}
