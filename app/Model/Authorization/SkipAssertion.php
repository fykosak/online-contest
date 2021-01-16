<?php

namespace FOL\Model\Authorization;

use Dibi\Exception;
use FOL\Model\ORM\TasksService;
use Nette\Security\Permission;
use Nette\Security\UserStorage;

class SkipAssertion {
    const MAX_SKIPPED = 10;

    private TasksService $tasksModel;

    private UserStorage $userStorage;

    public function __construct(TasksService $tasksModel, UserStorage $userStorage) {
        $this->tasksModel = $tasksModel;
        $this->userStorage = $userStorage;
    }

    /**
     * Check that the team has minimum score to skip.
     *
     * @param Permission $acl
     * @param mixed $role
     * @param mixed $resourceId
     * @param mixed $privilege
     * @return bool
     * @throws Exception
     */
    public function canSkip(Permission $acl, $role, $resourceId, $privilege): bool {
        $skipped = $this->tasksModel->findSkipped($this->userStorage->getState()[1]->id_team);
        return (count($skipped) < self::MAX_SKIPPED);
    }
}
