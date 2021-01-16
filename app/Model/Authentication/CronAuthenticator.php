<?php

namespace FOL\Model\Authentication;

use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

class CronAuthenticator extends AbstractAuthenticator {

    const ROLE = 'cron';

    private string $cronKey;

    public function __construct(string $cronKey, User $user) {
        parent::__construct($user);
        $this->cronKey = $cronKey;
    }

    protected function authenticate(array $credentials): Identity {
        [$key] = $credentials;
        if ((string)$key === (string)$this->cronKey) {
            return new Identity('cron', self::ROLE);
        }
        throw new AuthenticationException("Klíč se neshoduje.", IAuthenticator::INVALID_CREDENTIAL);
    }
}
