<?php

namespace App\Model\Authorization;

use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Context;
use App\Model\TeamsModel;
use App\Model\TasksModel;

class SkipAssertion
{
    const MAX_SKIPPED = 10;
    
    /**
     * @var TasksModel 
     */
    private $tasksModel;
    
    /**
     * @var User 
     */
    private $user;

    public function  __construct(TasksModel $tasksModel) {
        $this->tasksModel = $tasksModel;
    }
    
    public function setUser(User $user) {
        $this->user = $user;
    }
    
    /**
     * Check that the team has minimum score to skip.
     * 
     * @param \Nette\Security\Permission $acl
     * @param type $role
     * @param type $resourceId
     * @param type $privilege
     * @return type
     */
    public function canSkip(Permission $acl, $role, $resourceId, $privilege) : bool {
        $skipped = $this->tasksModel->findSkipped($this->user->getIdentity()->id_team);
        return (count($skipped) < self::MAX_SKIPPED);
    }
}