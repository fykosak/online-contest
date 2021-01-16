<?php

namespace FOL\Model\Authorization;

use Dibi\Exception;
use FOL\Model\ORM\TasksService;
use Nette\Security\IUserStorage;
use Nette\Security\Permission;

class SkipAssertion {
    const MAX_SKIPPED = 10;

    private TasksService $tasksModel;

    private IUserStorage $userStorage;

    public function __construct(TasksService $tasksModel, IUserStorage $userStorage) {
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
        $skipped = $this->tasksModel->findSkipped($this->userStorage->getIdentity()->id_team);
        return (count($skipped) < self::MAX_SKIPPED);
    }
}
