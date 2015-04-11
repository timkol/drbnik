<?php

namespace App\Model\AnimatedGossip;

use Nette;
use App\Model\GossipManager;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;

class AnimatedGossipFactory extends Nette\Object {
    
    /** @var Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }
    
    /**
     * 
     * @return \App\Model\AnimatedGossip\AnimatedGossip
     */
    public function create($previousId) {
        $gossipRow = $this->getGossipRow($previousId);
        return new AnimatedGossip($gossipRow->gossip_id, $gossipRow['gossip']);
    }
    
    /**
     * 
     * @return ActiveRow
     */
    private function getGossipRow($previousId) {
        $statusRow = $this->database->table('status')->where('name', 'approved')->fetch();
        
        $previousRow = $this->database->table('v_gossip_status')->where('gossip_id', $previousId)->fetch();
        
        $gossipTable = $this->database->table('v_gossip_status')->where('status_id', $statusRow->status_id)->order('modified');
        if(!$previousRow) {
            return $gossipTable->fetch();
        }
        return $gossipTable->where('modified > ?', $previousRow->modified)->fetch();
    }
}