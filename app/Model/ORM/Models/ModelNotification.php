<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;

/**
 * @property-read int notification_id
 * @property-read string message
 * @property-read string lang
 * @property-read string level
 * @property-read DateTimeInterface created
 */
final class ModelNotification extends AbstractModel {

    public function __toArray(): array {
        return [
            'message' => $this->message,
            'level' => $this->level,
            'lang' => $this->lang,
            'created' => $this->created->format('c'),
        ];
    }

}
