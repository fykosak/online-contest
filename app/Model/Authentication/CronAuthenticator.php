<?php

namespace FOL\Model\Authentication;

use Nette\Security\Authenticator;
use Nette\Security\AuthenticationException;
use Nette\Security\SimpleIdentity;
use Nette\Security\User;

class CronAuthenticator extends AbstractAuthenticator {

    const ROLE = 'cron';

    private string $cronKey;

    public function __construct(string $cronKey, User $user) {
        parent::__construct($user);
        $this->cronKey = $cronKey;
    }

    protected function authenticate(array $credentials): SimpleIdentity {
        [$key] = $credentials;
        if ((string)$key === (string)$this->cronKey) {
            return new SimpleIdentity('cron', self::ROLE);
        }
        throw new AuthenticationException('Klíč se neshoduje.', Authenticator::INVALID_CREDENTIAL);
    }
}
