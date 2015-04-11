<?php

namespace App\Model\AnimatedGossip;

use Nette;
use Nette\Utils\Strings;

class AnimatedGossip extends Nette\Object {

    private $gossip = "";
    
    private $id;
    
    private $gossipChars = array();
    private $parsedGossip;

    public function __construct($id, $gossip) {
        $this->gossip = $gossip;
        $this->id = $id;
        $this->chopString();
        $this->parse();
    }
    
    public function getLength() {
        return count($this->gossipChars);
    }
    
    public function getId() {
        return $this->id;
    }

    public function getParsed() {
        return $this->parsedGossip;
    }
    
    private function chopString() {
        $this->gossipChars = preg_split('/(?<!^)(?!$)/u', $this->gossip );
    }
    
    private function parse() {
        foreach ($this->gossipChars as $char) {
            $this->parsedGossip .= '<span>'.$char.'</span>';
        }
    }   

}
