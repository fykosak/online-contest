<?php

namespace App\Model\Authentication;

use Nette;

abstract class AbstractAuthenticator extends Nette\Object {
    /** @var Nette\Security\User */
    private $user;

    public function __construct(Nette\Security\User $user)
    {
        $this->user = $user;
    }
    
    public function login($id = NULL, $password = NULL) {
        $identity = $this->authenticate(func_get_args());
        $this->user->login($identity);
    }
    
    /**
     * @return Nette\Security\IIdentity
     * @throws Nette\Security\AuthenticationException
     */
    protected abstract function authenticate(array $credentials);
}