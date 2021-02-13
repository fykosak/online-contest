<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelTask;

class FOLScoreStrategy extends ScoreStrategy {

    protected function getPoints(ModelTask $task, int $wrongTries): int {
        $allowZeroes = $task->getGroup()->allow_zeroes;
        if (!$this->isHurryUp($allowZeroes)) {
            switch ($wrongTries) {
                case 0:
                    $score = $task->points;
                    break;
                case 1:
                    $score = ceil(0.6 * $task->points);
                    break;
                case 2:
                    $score = ceil(0.4 * $task->points);
                    break;
                case 3:
                    $score = ceil(0.2 * $task->points);
                    break;
                default:
                    $score = 0;
                    break;
            }
        } elseif ($task->points == 0) {
            return 0;
        } else {
            $score = $task->points - $wrongTries;
        }

        return ($allowZeroes) ? max(0, $score) : max(1, $score);
    }

    private function isHurryUp(bool $allowZeroes): bool {
        return $allowZeroes;
    }
}
