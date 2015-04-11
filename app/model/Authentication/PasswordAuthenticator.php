<?php

namespace App\Model\Authentication;

use Nette,
    Nette\Security\AuthenticationException,
    Nette\Security\IAuthenticator,
    Nette\Security\Identity,
    Nette\Security\Passwords;

class PasswordAuthenticator extends Nette\Object implements IAuthenticator
{
    const
	TABLE_LOGIN_NAME = 'login',
        TABLE_GRANT_NAME = 'grant',
	COLUMN_ID = 'login_id',
	COLUMN_NAME = 'login',
	COLUMN_PASSWORD_HASH = 'hash',
	COLUMN_ROLE = 'role',
        COLUMN_LAST_LOGIN = 'last_login';


    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
	$this->database = $database;
    }


    /**
     * Performs an authentication.
     * @return Nette\Security\Identity
     * @throws Nette\Security\AuthenticationException
     */
    public function authenticate(array $credentials)
    {
    	list($username, $password) = $credentials;

	$row = $this->database->table(self::TABLE_LOGIN_NAME)->where(self::COLUMN_NAME, $username)->fetch();

	if (!$row) {
            throw new AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

	//} else if ($row[self::COLUMN_PASSWORD_HASH] !== self::calculateHash($password, $row[self::COLUMN_ID])) {
        } 
        else if (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
	} 
        else if (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
            $row->update(array(
		self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
	}
        
        $row->update(array(
            self::COLUMN_LAST_LOGIN => date('Y-m-d H:i:s'),
        ));
        
        $rolesRow = $this->database->table(self::TABLE_GRANT_NAME)->where(self::COLUMN_ID, $row[self::COLUMN_ID]);
        $roles = array();
        foreach ($rolesRow as $role) {
            $roles[] = $role->role->name;
        }

        $personArray = (($row->person !== null)?$row->person->toArray():array());
	$arr = array_merge($row->toArray(), $personArray);
	unset($arr[self::COLUMN_PASSWORD_HASH]);
	return new Identity($row[self::COLUMN_ID], $roles, $arr);
    }

    /**
     * Adds new user.
     * @param  string
     * @param  string
     * @return void
     */
    public function add($username, $password)
    {
	try {
            $this->database->table(self::TABLE_LOGIN_NAME)->insert(array(
		self::COLUMN_NAME => $username,
		self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
            ));
	} 
        catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
	}
    }
    
    /**
     * Edits a user.
     * @param  string
     * @param  string
     * @return void
     */
    public function editMyself(Nette\Security\User $user, $oldPassword, $newUsername, $newPassword)
    {
        $row = $this->database->table(self::TABLE_LOGIN_NAME)->where(self::COLUMN_ID, $user->id)->fetch();
        
        if (!Passwords::verify($oldPassword, $row[self::COLUMN_PASSWORD_HASH])) {
            throw new AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);
	}
        
        $username = (($newUsername !== '')?$newUsername:$user->getIdentity()->data['login']);
        $passwordHash = (($newPassword !== '')?Passwords::hash($newPassword):$row[self::COLUMN_PASSWORD_HASH]);
        
	try {
            $row->update(array(
                self::COLUMN_NAME => $username,
		self::COLUMN_PASSWORD_HASH => $passwordHash,
            ));
	} 
        catch (Nette\Database\UniqueConstraintViolationException $e) {
            throw new DuplicateNameException;
	}
    }

}



class DuplicateNameException extends \Exception
{
    protected $message = "Duplicitní jméno.";
}
