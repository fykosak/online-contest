<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;
use Nette\Database\Table\Selection;

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
final class ModelTeam extends AbstractModel {

    public function getCompetitors(): GroupedSelection {
        return $this->related('competitor', 'id_team');
    }

    public function __toArray(): array {
        return [
            'teamId' => $this->id_team,
            'name' => $this->name,
            'category' => $this->category,
        ];
    }

    public function getCorrect(): GroupedSelection {
        return $this->related('answer')
            ->where('task.cancelled', 0)
            ->where('answer.correct', 1);
    }

    public function getCorrectOrSkipped(): GroupedSelection {
        return $this->related('answer')
            ->where('task.cancelled', 0)
            ->where('answer.correct ? OR answer.skipped ?', 1, 1);
    }

    public function getCorrectedInGroup(ModelGroup $group): GroupedSelection {
        return $this->getCorrect()
            ->where('task.id_group', $group->id_group);
    }

    public function getAnswers(): GroupedSelection {
        return $this->related('answer');
    }

    public function getAvailableTasks(): GroupedSelection {
        return $this->related('group_state')
            ->where('group:task.number <= group_state.task_counter')
            ->where('group:period.begin <= NOW()')
            ->where('group:period.end > NOW()');
    }

    public function getSubmitAvailableTasks(): Selection {
        $source = $this->getAvailableTasks();
        $source->where('group:task.id_task NOT IN ?', $this->getSolved()->fetchPairs('id_task', 'id_task'));
        return $source;
    }

    public function getTaskState(): GroupedSelection {
        return $this->related('task_state');
    }

    public function getSolved(): GroupedSelection {
        return $this->getTaskState()->where('points IS NOT NULL');
    }

    public function getSkipped(): GroupedSelection {
        return $this->getTaskState()->where('skipped = 1');
    }
}
