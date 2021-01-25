<?php

namespace FOL\Model;

use FOL\Model\ORM\Models\ModelTask;

class FOF2021ScoreStrategy extends ScoreStrategy {

    protected function getPoints(ModelTask $task, int $wrongTries): int {
        switch ($wrongTries) {
            case 0:
                return 5;
            case 1:
                return 3;
            case 2:
                return 2;
            default:
                return 1;
        }
    }
}
