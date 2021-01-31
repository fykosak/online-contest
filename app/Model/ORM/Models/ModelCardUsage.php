<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;

/**
 * @author Michal Červeňák <miso@fykos.cz>
 * @property-read int card_usage_id
 * @property-read string card_type
 * @property-read int team_id
 * @property-read DateTimeInterface created
 * @property-read string data
 */
final class ModelCardUsage extends AbstractModel {

    public const TYPE_SKIP = 'skip';
    public const TYPE_RESET = 'reset';
    public const TYPE_DOUBLE_POINTS = 'double_points';
    public const TYPE_ADD_TASK = 'add_task';
    public const TYPE_HINT = 'hint';
    public const TYPE_OPTIONS = 'options';

    public static function serializeData(array $values): string {
        return serialize($values);
    }

    public function getData(): array {
        return unserialize($this->data);
    }
}
