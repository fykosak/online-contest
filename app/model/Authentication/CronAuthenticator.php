<?php

namespace App\Model\Authentication;

use Nette\Security\IAuthenticator,
    Nette\Security\Identity,
    Nette\Security\AuthenticationException;

class CronAuthenticator extends AbstractAuthenticator
{

    const ROLE = 'cron';
    
    /** @var array */
    private $cronKey;
    
    public function __construct($cronKey, \Nette\Security\User $user) {
        parent::__construct($user);
        $this->cronKey = $cronKey;
    }

    protected function authenticate(array $credentials) {
	list($key) = $credentials;
        if((string) $key === (string) $this->cronKey) {
            return new Identity('cron', self::ROLE);
        }
        throw new AuthenticationException("Klíč se neshoduje.", IAuthenticator::INVALID_CREDENTIAL);
    }
}