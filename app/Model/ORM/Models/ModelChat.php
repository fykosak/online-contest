<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_chat
 * @property-read ActiveRow team
 * @property-read int|null id_parent
 * @property-read int id_team
 * @property-read bool org
 * @property-read string content
 * @property-read string lang
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 */
final class ModelChat extends AbstractModel {

    public function getTeam(): ModelTeam {
        /** @var ModelTeam $team */
        $team = ModelTeam::createFromActiveRow($this->team);
        return $team;
    }
}
