<?php
/**
 * @author Jan Papousek
 */

namespace App\Model\Authentication;

use Nette\Security\IAuthenticator,
    Nette\Security\Identity,
    Nette\Security\AuthenticationException,
    App\Model\Interlos;

class TeamAuthenticator extends AbstractAuthenticator
{

	const TEAM = "team";

	protected function authenticate(array $credentials) {
		$name		= $credentials[IAuthenticator::USERNAME];
		$password	= self::passwordHash($credentials[IAuthenticator::PASSWORD]);
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

	public static function passwordHash($password) {
		return sha1($password);
	}

}