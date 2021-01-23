<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_token
 * @property-read int id_team
 * @property-read ActiveRow $team
 * @property-read string token
 * @property-read DateTimeInterface not_before
 * @property-read DateTimeInterface not_after
 */
class ModelToken extends AbstractModel {

    public function getTeam(): ModelTeam {
        /** @var ModelToken $token */
        $token = ModelTeam::createFromActiveRow($this->team);
        return $token;
    }
}
