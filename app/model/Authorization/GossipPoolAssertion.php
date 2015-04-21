<?php

namespace App\Model\Authorization;

use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Context;
use App\Model\GossipToken\GossipToken;

class GossipPoolAssertion
{
    /**
     * @var Context
     */
    private $database;
    
    /** @var User */
    private $user;
    
    private $offset;

    public function __construct(Context $database) {
        $this->database = $database;        
    }
    
    public function setUser(User $user) {
        $this->user = $user;
        $this->offset = -$this->getCurrentWaitTime();
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
    public function canAdd(Permission $acl, $role, $resourceId, $privilege) {
//        $gossips = $this->database->query('SELECT s.name as status FROM v_gossip_status vs '
//                . 'JOIN gossip_author a USING(gossip_id) '
//                . 'JOIN login l ON l.person_id=a.author_id '
//                . 'JOIN status s USING(status_id)'
//                . 'WHERE l.login_id=? '
//                . 'ORDER BY modified', $this->user->id);
        
        $gossips = $this->database->query('SELECT s.name as status FROM v_gossip_status vs '
                . 'JOIN gossip_history h USING(gossip_id) '
                . 'JOIN status s ON vs.status_id=s.status_id '
                . 'JOIN status sh ON h.status_id=sh.status_id '
                . 'WHERE h.login_id = ? '
                . 'AND sh.name = "new" '
                . 'ORDER BY vs.modified', $this->user->id);
        
        foreach ($gossips as $gossip) {
            switch ($gossip->status) {
                case 'new':
                    $this->newPolicy();
                    break;
                case 'approved':
                    $this->approvedPolicy();
                    break;
                case 'rejected':
                    $this->rejectedPolicy();
                    break;
                case 'duplicit':
                    $this->duplicitPolicy();
                    break;
                default:
                    break;
            }
        }
        
        if($this->offset <= 0) {
            return true;
        }
        else {
            return false;
        }
    }
    
    private function getCurrentWaitTime() {
//        $newest = $this->database->table('gossip')
//                ->where(':gossip_author.person:login.login_id', $this->user->id)
//                ->order('inserted DESC')->fetch();
        $newest = $this->database->table('gossip_history')->where('status.name', 'new')
                ->where('login_id', $this->user->id)->order('modified DESC')->fetch();
//        $newest = $this->database->query('SELECT inserted FROM gossip '
//                . 'JOIN gossip_author a USING(gossip_id) '
//                . 'JOIN login l ON l.person_id=a.author_id '
//                . 'WHERE login_id=? '
//                . 'ORDER BY inserted DESC', $this->user->id)->fetch();
        if(!$newest) {
            return 0;
        }
        return time() - $newest->modified->getTimestamp();
    }

    private function rejectedPolicy() {
        $this->offset += 3*60;
    }
    
    private function approvedPolicy() {
        $this->offset -= 1*60;
    }
    
    private function newPolicy() {
        $this->offset += 1*60;
    }
    
    private function duplicitPolicy() {
        $this->newPolicy();
    }
}