<?php

namespace FOL\Model\Authentication;

use DateInterval;
use DateTime;
use Dibi\Connection;
use Dibi\DataSource;
use Dibi\Exception;
use FOL\Model\ORM\TeamsService;
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

    protected Connection $connection;

    protected TeamsService $teamsService;

    public function __construct(User $user, Connection $connection, TeamsService $teamsService) {
        parent::__construct($user);
        $this->connection = $connection;
        $this->teamsService = $teamsService;
    }

    /**
     * @param array $credentials
     * @return SimpleIdentity
     * @throws AuthenticationException
     * @throws Exception
     */
    protected function authenticate(array $credentials): SimpleIdentity {
        $name = $credentials[Authenticator::USERNAME];
        $password = self::passwordHash($credentials[Authenticator::PASSWORD]);
        $row = $this->teamsService->findAll()->where('[name] = %s', $name)->fetch();
        if (empty($row)) {
            throw new AuthenticationException(
                sprintf('Tým %s neexistuje.', $name),
                Authenticator::IDENTITY_NOT_FOUND
            );
        }
        if ($row['password'] != $password) {
            throw new AuthenticationException(
                'Heslo se neshoduje.',
                Authenticator::INVALID_CREDENTIAL
            );
        }
        return new SimpleIdentity($name, self::TEAM, ['id_team' => $row['id_team'], 'role' => self::TEAM]);
    }

    /**
     * @param $token
     * @return void
     * @throws AuthenticationException
     * @throws Exception
     */
    public function authenticateByToken($token): void {
        $res = $this->findValidRecoveryTokens()->where('[token] = %s', $token)->fetch();
        if (empty($res)) {
            throw new AuthenticationException(
                sprintf('Token %s není validní.', $token),
                Authenticator::INVALID_CREDENTIAL
            );
        }
        $this->connection->delete('token')->where('[id_token] = %i', $res['id_token'])->execute();

        $team = $this->teamsService->find($res['id_team']);
        $identity = new SimpleIdentity($team['name'], self::TEAM, ['id_team' => $team['id_team'], 'role' => self::TEAM]);
        $this->user->login($identity);
    }

    /**
     * @param $teamId
     * @return string|null
     * @throws Exception
     */
    public function createRecoveryToken($teamId): ?string {
        $token = Random::generate(self::TOKEN_LENGTH);
        if ($this->findValidRecoveryTokens()->where('[id_team] = %i', $teamId)->fetch()) {
            return null;
        }

        $this->connection->insert('token', [
            'id_team' => $teamId,
            'token' => $token,
            'not_before' => new DateTime(),
            'not_after' => (new DateTime())->add(new DateInterval(self::TOKEN_LIFETIME)),
        ])->execute();
        return $token;
    }

    public static function passwordHash(string $password): string {
        return sha1($password);
    }

    /**
     * @return DataSource
     * @throws Exception
     */
    private function findValidRecoveryTokens(): DataSource {
        return $this->connection->dataSource('SELECT * FROM [token] WHERE [not_before] <= NOW() AND [not_after] >= NOW()');
    }

}
