<?php
namespace App\Repositories;

use App\Model\User;

class UsersRepository extends CommonRepository {

    static function getEntityClassNames() :array {
        return [User::class];
    }

    public function getByUsername($email) {
        return $this->getBy([
            "email" => $email
        ]);
    }

    /**
     * @param $username
     * @param $email
     * @param $password
     * @return User
     */
    public function registerUser($email, $password) {
        $user = new User();
        $user->email = $email;
        $user->setNewPassword($password);
        $user->role = User::ROLE_USER;

        return $this->persistAndFlush($user);
    }

    /**
     * @param $token
     * @param $token2
     * @return \Nextras\Orm\Entity\IEntity|null
     * @throws \Exception
     */
    public function getByTokens($token, $token2) {
        $dt = new \DateTime();
        $dt->modify('-2 days'); //Token expiration time
        return $this->getBy([
            'passwordResetToken' => $token,
            'passwordResetToken2' => $token2,
            'passwordResetRequested>=' => $dt
        ]);
    }
}