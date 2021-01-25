<?php

namespace FOL\Model\ORM\Models;

use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;

/**
 * @property-read int id_task
 * @property-read ActiveRow task
 * @property-read int id_team
 * @property-read ActiveRow team
 * @property-read bool skipped
 * @property-read bool substitute
 * @property-read int points
 * @property-read \DateTimeInterface inserted
 */
class ModelTaskState extends AbstractModel {

    public function getTask(): ModelTask {
        /** @var ModelTask $task */
        $task = ModelTask::createFromActiveRow($this->task);
        return $task;
    }

    public function getTeam(): ModelTeam {
        /** @var ModelTeam $task */
        $team = ModelTeam::createFromActiveRow($this->task);
        return $team;
    }

    public function __toArray(): array {
        return [
            'taskId' => $this->id_task,
            'teamId' => $this->id_team,
            'skipped' => $this->skipped,
            'points' => $this->points,
        ];
    }
}
