<?php

namespace App\Model;
use Nette;

class GossipManager extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var Nette\Security\User */
    private $user;


    public function __construct(Nette\Database\Context $database, Nette\Security\User $user)
    {
	$this->database = $database;
        $this->user = $user;
    }
    
    /**
     * 
     * @param string $gossip
     * @param array $authors
     * @param array $victims
     */
    public function add($gossip, $authors, $victims) {
        $statusNew = $this->database->table('status')->where('name', 'new')->fetch();
        
        $gossipInsert = $this->database->table('gossip')->insert(array(
            'gossip' => $gossip,
            //'status_id' => $statusNew->status_id
        ));
        $this->database->table('gossip_history')->insert(array(
            'gossip_id' => $gossipInsert->gossip_id,
            'status_id' => $statusNew->status_id,
            'login_id' => $this->user->id, //TODO logged out
        ));
        foreach ($authors as $author) {
            $this->database->table('gossip_author')->insert(array(
                'gossip_id' => $gossipInsert->gossip_id,
                'author_id' => $author
            ));
        }
        foreach ($victims as $victim) {
            $this->database->table('gossip_victim')->insert(array(
                'gossip_id' => $gossipInsert->gossip_id,
                'victim_id' => $victim
            ));
        }
    }
    
    public function changeStatus($gossipId, $status) {
        if($status === null) {
            return;
        }
        
        $statusRow = $this->database->table('status')->where('name', $status)->fetch();
        
        $this->database->table('gossip_history')->insert(array(
            'gossip_id' => $gossipId,
            'status_id' => $statusRow->status_id,
            'login_id' => $this->user->id,
        ));
        
//        $this->database->table('gossip')->where('gossip_id', $gossipId)->update(array(
//            'status_id' => $statusRow->status_id,
//        ));
    }
    
    /**
     * 
     * @param string|null $status
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getByStatus($status = null, $modifiedMax = null, $modifiedMin = null) {
        if($modifiedMax === null) {
            $modifiedMax = date('Y-m-d H:i:s');
        }
        if($modifiedMin === null) {
            $modifiedMin = date('Y-m-d H:i:s', 1);
        }
        
        $statusRow = $this->database->table('status')->where('name', $status)->fetch();
        return $this->database->table('v_gossip_status')->where('status_id', $statusRow->status_id)
                ->where('modified <= ?', $modifiedMax)->where('modified > ?', $modifiedMin);
    }
    
    /**
     * 
     * @param string|null $status
     * @return \Nette\Database\ResultSet
     */
    public function getByAuthor($author_id = null, $status = null, $modified = null) {
        if($modified === null) {
            $modified = date('Y-m-d H:i:s');
        }
        
        $statusRow = $this->database->table('status')->where('name', $status)->fetch();
        $gossips = $this->database->query('SELECT vs.* FROM v_gossip_status vs '
                . 'JOIN gossip_author ga USING(gossip_id) '
                . 'WHERE ga.author_id = ? '
                . 'AND vs.status_id = ? '
                . 'AND vs.modified <= ? '
                . 'ORDER BY vs.modified', $author_id, $statusRow->status_id, $modified);
        return $gossips;
    }
    
    /**
     * 
     * @param string|null $status
     * @return \Nette\Database\ResultSet
     */
    public function getByVictim($author_id = null, $status = null, $modified = null) {
        if($modified === null) {
            $modified = date('Y-m-d H:i:s');
        }
        
        $statusRow = $this->database->table('status')->where('name', $status)->fetch();
        $gossips = $this->database->query('SELECT vs.* FROM v_gossip_status vs '
                . 'JOIN gossip_victim gv USING(gossip_id) '
                . 'WHERE gv.victim_id = ? '
                . 'AND vs.status_id = ? '
                . 'AND vs.modified <= ? '
                . 'ORDER BY vs.modified', $author_id, $statusRow->status_id, $modified);
        return $gossips;
    }
    
    /**
     * 
     * @param \Nette\Database\Table\ActiveRow $person
     * @return string
     */
    public function getPersonDisplayName($person) {
        $name = $person->display_name;
        if($name === null) {
            $name = $person->other_name .' '. $person->family_name;
        }
        return $name;
    }
}