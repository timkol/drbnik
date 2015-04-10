<?php

namespace App\Model;
use Nette;

class GossipManager extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;


    public function __construct(Nette\Database\Context $database)
    {
	$this->database = $database;
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
            'status_id' => $statusNew->status_id
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
        
        $this->database->table('gossip')->where('gossip_id', $gossipId)->update(array(
            'status_id' => $statusRow->status_id,
        ));
    }
    
    /**
     * 
     * @param string|null $status
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getByStatus($status = null) {
        return $this->database->table('gossip')->where('status.name', $status);
    }
}