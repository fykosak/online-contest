<?php

namespace App\Model\Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\IIdentity;
use Nette\Security\User;
use Nette\SmartObject;
use Tracy\Debugger;

abstract class AbstractAuthenticator {
    use SmartObject;

    protected User $user;

    public function __construct(User $user) {
        $this->user = $user;
    }

    /**
     * @param null $id
     * @param null $password
     * @return void
     * @throws AuthenticationException
     */
    public function login($id = null, $password = null): void {
        $identity = $this->authenticate(func_get_args());
        $this->user->login($identity);
    }

    /**
     * @param array $credentials
     * @return IIdentity
     * @throws AuthenticationException
     */
    protected abstract function authenticate(array $credentials): IIdentity;
}
