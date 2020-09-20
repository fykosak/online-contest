<?php

namespace App\Model\Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\IIdentity;
use Nette\Security\User;
use Nette\SmartObject;

abstract class AbstractAuthenticator {
    use SmartObject;

    protected User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function login($id = null, $password = null): void {
        $identity = $this->authenticate(func_get_args());
        $this->user->login($identity);
    }

    /**
     * @return IIdentity
     * @throws AuthenticationException
     */
    protected abstract function authenticate(array $credentials);
}
