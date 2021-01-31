<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_competitor
 * @property-read int id_team
 * @property-read ActiveRow team
 * @property-read int id_school
 * @property-read ActiveRow school
 * @property-read string name
 * @property-read string email
 * @property-read int study_year
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 */
final class ModelCompetitor extends AbstractModel {

    public function getTeam(): ModelTeam {
        /** @var ModelTeam $team */
        $team = ModelTeam::createFromActiveRow($this->team);
        return $team;
    }
}
