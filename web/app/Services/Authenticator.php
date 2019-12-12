<?php
namespace App\Services;

use App\Model\User;
use App\Repositories\UsersRepository;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\Passwords;
use Nette\SmartObject;

class Authenticator implements IAuthenticator {
    use SmartObject;

    /**
     * @var UsersRepository
     */
    private $usersRepository;

    /**
     * Authenticator constructor.
     * @param UsersRepository $usersRepository
     */
    public function __construct(UsersRepository $usersRepository) {
        $this->usersRepository = $usersRepository;
    }

    /**
     * @param array $credentials
     * @return Identity
     * @throws AuthenticationException
     */
    function authenticate(array $credentials) {
        list($username, $password) = $credentials;

        /**
         * @var User
         */
        $user = $this->usersRepository->getByUsername($username);
        if (!$user) {
            throw new AuthenticationException("User with this username not found");
        }

        if (!Passwords::verify($password, $user->password)) {
            throw new AuthenticationException("Incorrect password");
        }

        $values = $user->toIdentityArray();

        return new Identity(
            $user->id,
            $user->role,
            $values
        );
    }
}