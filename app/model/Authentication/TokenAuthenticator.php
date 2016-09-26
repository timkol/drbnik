<?php

namespace App\Model\Authentication;

use Nette\Security\IAuthenticator,
    Nette\Security\Identity,
    Nette\Security\AuthenticationException;

class TokenAuthenticator extends \Nette\Object
{
    private $token;
    private $role;
    private $userId;

    /** @var Nette\Security\User */
    private $user;
    
    public function __construct($tokenAuth, \Nette\Security\User $user) {
        $this->user = $user;
        $this->token = $tokenAuth['token'];
        $this->role = $tokenAuth['role'];
        $this->userId = $tokenAuth['user'];
    }
    
    public function login($id = NULL, $password = NULL) {
        $identity = $this->authenticate(func_get_args());
        $this->user->login($identity);
    }

    protected function authenticate(array $credentials) {
	list($key) = $credentials;
        if((string) $key === (string) $this->token) {
            return new Identity($this->userId, $this->role);
        }
        throw new AuthenticationException("Klíč se neshoduje.", IAuthenticator::INVALID_CREDENTIAL);
    }
}