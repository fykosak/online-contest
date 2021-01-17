<?php

namespace FOL\Model\ORM\Models;

use Fykosak\Utils\ORM\AbstractModel;

/**
 * @property-read int id_year
 * @property-read string name
 * @property-read \DateTimeInterface registration_start
 * @property-read \DateTimeInterface registration_end
 * @property-read \DateTimeInterface game_start
 * @property-read \DateTimeInterface game_end
 * @property-read \DateTimeInterface inserted
 * @property-read \DateTimeInterface updated
 */
class ModelYear extends AbstractModel {

    public function isGameActive(): bool {
        return $this->isGameStarted() && !$this->isGameEnd();
    }

    public function isGameEnd(): bool {
        return time() > strtotime($this->game_end);
    }

    public function isGameStarted(): bool {
        return strtotime($this->game_start) < time();
    }

    public function isRegistrationActive(): bool {
        return $this->isRegistrationStarted() && !$this->isRegistrationEnd();
    }

    public function isRegistrationEnd(): bool {
        return strtotime($this->registration_end) < time();
    }

    public function isRegistrationStarted(): bool {
        return strtotime($this->registration_start) < time();
    }

}
