<?php

namespace FOL\Model\ORM\Models;

use DateTimeInterface;
use Fykosak\Utils\ORM\AbstractModel;
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

    public function getCardUsageByType(string $type): ?ModelCardUsage {
        $row = $this->related('card_usage')->where('card_type', $type)->fetch();
        return $row ? ModelCardUsage::createFromActiveRow($row) : null;
    }

    public function getCorrect(): GroupedSelection {
        return $this->related('answer')
            ->where('task.cancelled', 0)
            ->where('answer.correct', 1);
    }

    public function getAvailableTasks(): GroupedSelection {
        return $this->related('group_state')
            ->where('group:task.number <= group_state.task_counter')
            ->where('group:period.begin <= NOW()')
            ->where('group:period.end > NOW()');
    }

    public function getSubmitAvailableTasks(): GroupedSelection {
        $source = $this->getAvailableTasks();
        $source->where('group:task.id_task NOT', $this->getSolvedOrSkipped()->fetchPairs('id_task', 'id_task'));
        return $source;
    }

    public function getTaskState(): GroupedSelection {
        return $this->related('task_state');
    }

    public function getSolved(): GroupedSelection {
        return $this->getTaskState()->where('task_state.points IS NOT NULL');
    }

    public function getSkipped(): GroupedSelection {
        return $this->getTaskState()->where('skipped = 1');
    }

    public function getSolvedOrSkipped(): GroupedSelection {
        return $this->getTaskState()->where('task_state.points IS NOT NULL OR task_state.skipped = 1');
    }
    public function getSolvedOrSkippedOrCanceled(): GroupedSelection {
        return $this->getTaskState()->where('task_state.points IS NOT NULL OR task_state.skipped = 1 OR task.cancelled = 1');
    }
}
