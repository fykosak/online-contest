<?php

namespace FOL\Model\Authentication;

use DateInterval;
use DateTime;
use FOL\Model\ORM\Models\ModelTeam;
use FOL\Model\ORM\Models\ModelToken;
use FOL\Model\ORM\Services\ServiceTeam;
use FOL\Model\ORM\Services\ServiceToken;
use Fykosak\Utils\ORM\TypedTableSelection;
use Nette\Security\Authenticator;
use Nette\Security\SimpleIdentity;
use Nette\Security\AuthenticationException;
use Nette\Utils\Random;
use Nette\Security\User;

/**
 * @author Jan Papousek
 */
class TeamAuthenticator extends AbstractAuthenticator {

    const TEAM = 'team';
    const TOKEN_LENGTH = 10;
    const TOKEN_LIFETIME = 'PT10M';

    private ServiceTeam $serviceTeam;
    private ServiceToken $serviceToken;

    public function __construct(User $user, ServiceTeam $serviceTeam, ServiceToken $serviceToken) {
        parent::__construct($user);
        $this->serviceTeam = $serviceTeam;
        $this->serviceToken = $serviceToken;
    }

    /**
     * @param array $credentials
     * @return SimpleIdentity
     * @throws AuthenticationException
     */
    protected function authenticate(array $credentials): SimpleIdentity {
        $name = $credentials[Authenticator::USERNAME];
        $password = self::passwordHash($credentials[Authenticator::PASSWORD]);
        /** @var ModelTeam $modelTeam */
        $modelTeam = $this->serviceTeam->getTable()->where('name', $name)->fetch();
        if (!isset($modelTeam)) {
            throw new AuthenticationException(
                sprintf('Tým %s neexistuje.', $name),
                Authenticator::IDENTITY_NOT_FOUND
            );
        }
        if ($modelTeam->password != $password) {
            throw new AuthenticationException(
                'Heslo se neshoduje.',
                Authenticator::INVALID_CREDENTIAL
            );
        }
        return new SimpleIdentity($name, self::TEAM, ['id_team' => $modelTeam->id_team, 'role' => self::TEAM]);
    }

    /**
     * @param string $token
     * @return void
     * @throws AuthenticationException
     */
    public function authenticateByToken(string $token): void {
        /** @var ModelToken $res */
        $res = $this->findValidRecoveryTokens()->where('token', $token)->fetch();
        if (!$res) {
            throw new AuthenticationException(
                sprintf('Token %s není validní.', $token),
                Authenticator::INVALID_CREDENTIAL
            );
        }
        $this->serviceToken->dispose($res);

        /** @var ModelTeam $modelTeam */
        $modelTeam = $this->serviceTeam->findByPrimary($res['id_team']);
        $identity = new SimpleIdentity($modelTeam->name, self::TEAM, ['id_team' => $modelTeam->id_team, 'role' => self::TEAM]);
        $this->user->login($identity);
    }

    public function createRecoveryToken(ModelTeam $team): ?ModelToken {
        $token = Random::generate(self::TOKEN_LENGTH);
        if ($this->findValidRecoveryTokens()->where('id_team', $team->id_team)->fetch()) {
            return null;
        }
        /** @var ModelToken $modelToken */
        $modelToken = $this->serviceToken->createNewModel([
                'id_team' => $team->id_team,
                'token' => $token,
                'not_before' => new DateTime(),
                'not_after' => (new DateTime())->add(new DateInterval(self::TOKEN_LIFETIME)),
            ]
        );
        return $modelToken;
    }

    public static function passwordHash(string $password): string {
        return sha1($password);
    }

    private function findValidRecoveryTokens(): TypedTableSelection {
        return $this->serviceToken->getTable()->where('not_before <= NOW()')->where('not_after >= NOW()');
    }
}
