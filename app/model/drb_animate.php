<?php

namespace App\Model;

use Nette;
use Nette\Utils\Strings;

class drb_animate extends Nette\Object {

    private $gossip = "";
    private $gossipChars = array();
    private $parsedGossip;

    public function __construct($gossip) {
        $this->gossip = $gossip;
        $this->chopString();
        $this->parse();
    }
    
    public function getLength() {
        return count($this->gossipChars);
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

//    public function parse_drb() {
//        $this->parsed_drb = str_split($this->drb);
//        return $this->parsed_drb;
//    }
//    
//    public function split_drb(){
//        $r='';
//        foreach ($this->parsed_drb as $value){
//            $r.='<span>'.$value.'</span>';
//        }
//        return $r;
//        
//    }
    

}
