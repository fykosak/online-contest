<?php

namespace FOL\Model\Authentication;

use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class OrgAuthenticator extends AbstractAuthenticator {

    const ROLE = 'org';

    private array $userList;

    public function __construct(array $userList, User $user) {
        parent::__construct($user);
        $this->userList = $userList;
    }

    protected function authenticate(array $credentials): Identity {
        [$username, $password] = $credentials;
        foreach ($this->userList as $name => $pass) {
            if (strcasecmp($name, $username) === 0) {
                if ((string)$pass === (string)$password) {
                    return new Identity($name, self::ROLE);
                } else {
                    throw new AuthenticationException(
                        "Heslo se neshoduje.",
                        IAuthenticator::INVALID_CREDENTIAL
                    );
                }
            }
        }
        throw new AuthenticationException(
            "Org '$username' neexistuje.",
            IAuthenticator::IDENTITY_NOT_FOUND
        );
    }
}
