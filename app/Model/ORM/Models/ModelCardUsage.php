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

    // array of tasks
    public const TYPE_SKIP = 'skip';
    // task
    public const TYPE_RESET = 'reset';
    public const TYPE_HINT = 'hint';
    public const TYPE_OPTIONS = 'options';
    // group
    public const TYPE_ADD_TASK = 'add_task';
    // answer
    public const TYPE_DOUBLE_POINTS = 'double_points';

    /**
     * @return int[]|int
     */
    public function getData() {
        switch ($this->card_type) {
            case self::TYPE_SKIP:
                return explode(',', $this->data);
            default:
                return +$this->data;
        }
    }

    /**
     * @param string $type
     * @param array|int $data
     * @return string
     */
    public static function serializeData(string $type, $data): string {
        switch ($type) {
            case self::TYPE_SKIP:
                return join(',', array_keys(array_filter($data, fn($v) => (bool)$v)));
            case self::TYPE_DOUBLE_POINTS:
                return $data['answer'];
            case self::TYPE_ADD_TASK:
                return $data['group'];
            default:
                return $data['task'];
        }
    }
}
