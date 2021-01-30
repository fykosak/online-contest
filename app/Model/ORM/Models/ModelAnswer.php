<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_answer
 * @property-read int id_team
 * @property-read ActiveRow team
 * @property-read int id_task
 * @property-read ActiveRow task
 * @property-read string|null answer_str
 * @property-read int|null answer_int
 * @property-read float|null answer_real
 * @property-read bool correct
 * @property-read DateTimeInterface inserted
 * @property-read DateTimeInterface updated
 * @property-read bool double_points
 */
class ModelAnswer extends AbstractModel {

    public function getTeam(): ModelTeam {
        /** @var ModelTeam $team */
        $team = ModelTeam::createFromActiveRow($this->team);
        return $team;
    }

    public function getTask(): ModelTask {
        /** @var ModelTask $task */
        $task = ModelTask::createFromActiveRow($this->task);
        return $task;
    }
}
