<?php
namespace App\Model;

use Nette\Security\Passwords;
use Nextras\Dbal\Utils\DateTimeImmutable;


/**
 * Class User
 * @package App\Model
 * @property string|null $email
 * @property string $password
 * @property string $role {enum static::ROLE_*}
 * @property string|null $passwordResetToken {default null}
 * @property string|null $passwordResetToken2 {default null}
 * @property DateTimeImmutable|null $passwordResetRequested {default null}
 */
class User extends CommonModel {
    const ROLE_ROOT = 'root';
    const ROLE_ADMIN = 'admin';
    const ROLE_USER = 'user';

    /**
     * @param string $password
     */
    public function setNewPassword($password) {
        $this->password = Passwords::hash($password);
    }

    public function toIdentityArray() {
        $values = $this->toArray();
        unset($values['password']);
        return $values;
    }
}
