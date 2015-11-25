<?php

namespace App\Model\Authorization;

use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Context;
use App\Model\TeamsModel;

class SkipAssertion
{
    const MIN_SCORE = -7;
    
    private $connection;
    private $user;

    public function  __construct(\DibiConnection $connection) {
        $this->connection = $connection;
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
    public function canSkip(Permission $acl, $role, $resourceId, $privilege) {
        $score = $this->connection->query("SELECT score FROM [team] WHERE id_team = %i", $this->user->id)->fetch();
        return ($score >= self::MIN_SCORE);
    }
}