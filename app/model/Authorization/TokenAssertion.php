<?php

namespace App\Model\Authorization;

use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Context;
use App\Model\GossipToken\GossipToken;

class TokenAssertion
{
    /**
     * @var Context
     */
    private $database;
    
    /**
     *
     * @var GossipToken 
     */
    private $token;

    public function __construct(Context $database, GossipToken $token) {
        $this->database = $database;
        $this->token = $token;
    }
    
    /**
     * Check that the person is the person of logged user.
     * 
     * @note Grant contest is ignored in this context (i.e. person is context-less).
     * 
     * @param \Nette\Security\Permission $acl
     * @param type $role
     * @param type $resourceId
     * @param type $privilege
     * @return type
     */
    public function isCooledDown(Permission $acl, $role, $resourceId, $privilege) {
        return $this->token->isCooledDown();
    }
}