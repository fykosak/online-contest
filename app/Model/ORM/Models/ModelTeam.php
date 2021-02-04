<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\GroupedSelection;

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
}
