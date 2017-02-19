<?php

namespace App\Model\Authentication;

use Nette\Utils\Random;
use Nette\Database\Context;
use Nette\Security\User;
use Nette\Database\Table\ActiveRow;
use Nette\Security\AuthenticationException;
use Nette\Security\Identity;
use Nette\Security\IAuthenticator;

class TokenAuthenticator extends \Nette\Object
{
    const
	TABLE_AUTH_TOKEN_NAME = 'auth_token',
        COLUMN_TYPE = 'type',
	COLUMN_TOKEN = 'token',
        TYPE_WEBSOCKET = 'WS',
        TYPE_PHP = 'PHP',
        TYPE_PERMANENT = 'PHP-PERM';

    /** @var User */
    private $user;
    
    /** @var Context */
    private $database;
    
    public function __construct(User $user, Context $database) {
        $this->user = $user;
        $this->database = $database;
    }
    
    public function login($id = NULL, $password = NULL) {
        $identity = $this->authenticate(func_get_args());
        $this->user->login($identity);
    }

    protected function authenticate(array $credentials) {
	list($key) = $credentials;
        /* @var $authTokenRow ActiveRow */
        $authTokenRow = $this->database->table(self::TABLE_AUTH_TOKEN_NAME)
                ->where(self::COLUMN_TOKEN, $key)
                ->where(self::COLUMN_TYPE, [self::TYPE_PERMANENT, self::TYPE_PHP])->fetch();        
        if(!$authTokenRow) {
            throw new AuthenticationException("Klíč se neshoduje.", IAuthenticator::INVALID_CREDENTIAL);
        }
        
        $loginRow = $authTokenRow->ref(PasswordAuthenticator::TABLE_LOGIN_NAME, PasswordAuthenticator::COLUMN_ID);
        
        if($authTokenRow[self::COLUMN_TYPE] !== self::TYPE_PERMANENT) {
            $authTokenRow->delete();
        }
        
        $loginRow->update(array(
            PasswordAuthenticator::COLUMN_LAST_LOGIN => date('Y-m-d H:i:s'),
        ));
        
        $roles = array();
        foreach ($loginRow->related(PasswordAuthenticator::TABLE_GRANT_NAME, PasswordAuthenticator::COLUMN_ID) as $role) {
            $roles[] = $role->role->name;
        }

        $personArray = (($loginRow->person !== null)?$loginRow->person->toArray():array());
	$arr = array_merge($loginRow->toArray(), $personArray);
	unset($arr[PasswordAuthenticator::COLUMN_PASSWORD_HASH]);
	return new Identity($loginRow[PasswordAuthenticator::COLUMN_ID], $roles, $arr);
    }
    
    public function addToken($type) {
        $token = self::generateToken();
        $this->database->table(self::TABLE_AUTH_TOKEN_NAME)->insert(array(
            self::COLUMN_TYPE => $type,
            self::COLUMN_TOKEN => $token,
            PasswordAuthenticator::COLUMN_ID => $this->user->id
        ));
        return $token;
    }
    
    private static function generateToken($length=100) {
        return Random::generate($length);
    }
}