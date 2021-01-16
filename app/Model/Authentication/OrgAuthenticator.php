<?php

namespace FOL\Model\Authentication;

use Nette\Security\Authenticator;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class OrgAuthenticator extends AbstractAuthenticator {

    const ROLE = 'org';

    private array $userList;

    public function __construct(array $userList, User $user) {
        parent::__construct($user);
        $this->userList = $userList;
    }

    protected function authenticate(array $credentials): SimpleIdentity {
        [$username, $password] = $credentials;
        foreach ($this->userList as $name => $pass) {
            if (strcasecmp($name, $username) === 0) {
                if ((string)$pass === (string)$password) {
                    return new SimpleIdentity($name, self::ROLE);
                } else {
                    throw new AuthenticationException(
                        "Heslo se neshoduje.",
                        Authenticator::INVALID_CREDENTIAL
                    );
                }
            }
        }
        throw new AuthenticationException(
            "Org '$username' neexistuje.",
            Authenticator::IDENTITY_NOT_FOUND
        );
    }
}
