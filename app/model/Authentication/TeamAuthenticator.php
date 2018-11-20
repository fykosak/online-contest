<?php

/**
 * @author Jan Papousek
 */

namespace App\Model\Authentication;

use Nette\Security\IAuthenticator,
    Nette\Security\Identity,
    Nette\Security\AuthenticationException,
    Nette\Utils\Random,
    App\Model\Interlos;

class TeamAuthenticator extends AbstractAuthenticator
{

    const TEAM = "team";
    const TOKEN_LENGTH = 10;
    const TOKEN_LIFETIME = 'PT10M';

    /**
     * @var \DibiConnection
     */
    protected $connection;

    public function __construct(\Nette\Security\User $user, \DibiConnection $connection) {
        parent::__construct($user);
        $this->connection = $connection;
    }

    protected function authenticate(array $credentials) {
        $name = $credentials[IAuthenticator::USERNAME];
        $password = self::passwordHash($credentials[IAuthenticator::PASSWORD]);
        $row = Interlos::teams()->findAll()->where("[name] = %s", $name)->fetch();
        if (empty($row)) {
            throw new AuthenticationException(
                "Tým '$name' neexistuje.",
                IAuthenticator::IDENTITY_NOT_FOUND
            );
        }
        if ($row["password"] != $password) {
            throw new AuthenticationException(
                "Heslo se neshoduje.",
                IAuthenticator::INVALID_CREDENTIAL
            );
        }
        return new Identity($name, self::TEAM, array("id_team" => $row["id_team"], "role" => self::TEAM));
    }

    public function authenticateByToken($token) {
        $res = $this->findValidRecoveryTokens()->where("[token] = %s", $token)->fetch();
        if (empty($res)) {
            throw new AuthenticationException(
                "Token '$token' není validní.",
                IAuthenticator::INVALID_CREDENTIAL
            );
        }
        $this->connection->delete("token")->where("[id_token] = %i", $res['id_token'])->execute();

        $team = Interlos::teams()->find($res['id_team']);
        $identity = new Identity($team['name'], self::TEAM, array("id_team" => $team["id_team"], "role" => self::TEAM));
        $this->user->login($identity);
    }

    public function createRecoveryToken($teamId) {
        $token = Random::generate(self::TOKEN_LENGTH);
        if ($this->findValidRecoveryTokens()->where("[id_team] = %i", $teamId)->fetch()) {
            return null;
        }

        $this->connection->insert('token', [
            'id_team' => $teamId,
            'token' => $token,
            'not_before' => new \DateTime(),
            'not_after' => (new \DateTime())->add(new \DateInterval(self::TOKEN_LIFETIME))
        ])->execute();
        return $token;
    }

    public static function passwordHash($password) {
        return sha1($password);
    }

    /** @return \DibiDataSource */
    private function findValidRecoveryTokens() {
        return $this->connection->dataSource("SELECT * FROM [token] WHERE [not_before] <= NOW() AND [not_after] >= NOW()");
    }

}
