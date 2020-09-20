<?php

namespace App\Model\Authorization;

use Nette\Security\Permission;
use Nette\Security\User;
use App\Model\TasksModel;

class SkipAssertion {
    const MAX_SKIPPED = 10;

    private TasksModel $tasksModel;

    private User $user;

    public function __construct(TasksModel $tasksModel) {
        $this->tasksModel = $tasksModel;
    }

    public function setUser(User $user): void {
        $this->user = $user;
    }

    /**
     * Check that the team has minimum score to skip.
     *
     * @param Permission $acl
     * @param mixed $role
     * @param mixed $resourceId
     * @param mixed $privilege
     * @return bool
     */
    public function canSkip(Permission $acl, $role, $resourceId, $privilege): bool {
        $skipped = $this->tasksModel->findSkipped($this->user->getIdentity()->id_team);
        return (count($skipped) < self::MAX_SKIPPED);
    }
}
