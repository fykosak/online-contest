<?php

namespace FOL\Model\Authentication;

use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;
use Nette\SmartObject;

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
        $identity = $this->authenticate([$id, $password]);
        $this->user->login($identity);
    }

    /**
     * @param array $credentials
     * @return SimpleIdentity
     * @throws AuthenticationException
     */
    protected abstract function authenticate(array $credentials): SimpleIdentity;
}
