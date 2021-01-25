<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_team
 * @property-read int id_year
 * @property-read string name
 * @property-read string password
 * @property-read string category
 * @property-read string email
 * @property-read string address
 * @property-read bool disqualified
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 * @property-read int score_exp
 */
class ModelTeam extends AbstractModel {

    /**
     * @param ActiveRow|ModelTeam $row
     * @return array
     */
    public static function __toArray(ActiveRow $row): array {
        return [
            'teamId' => $row->id_team,
            'name' => $row->name,
            'category' => $row->category,
        ];
    }
}
