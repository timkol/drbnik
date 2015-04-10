<?php

namespace App\Model\GossipToken;

use Nette\InvalidStateException;
use Nette\Security\Permission;
use Nette\Security\User;
use Nette\Database\Context;
use Nette\Http\Request;
use Nette\Http\Response;
use Nette\Utils\Random;

class GossipToken
{
    /**
     * @var Context
     */
    private $database;
    
    /**
     *
     * @var Request 
     */
    private $request;
    
    /**
     *
     * @var Response 
     */
    private $response;

    public function __construct(Context $database, Request $request, Response $response) {
        $this->database = $database;
        $this->request = $request;
        $this->response = $response;
    }
    
    public function isCooledDown() {
        return ($this->getRemainingCooldown()->format('%r') === '');
    }    
    
    public function generateToken() {
        $oldToken = $this->request->getCookie('gossipToken');
        
        $oldTokenId = null;
        if($oldToken !== null) {
            $oldRow = $this->database->table('token')->where('token', $oldToken)->fetch();
            $oldTokenId = $oldRow->token_id;
        }
        
        do {
            $newToken = Random::generate();
            $temp = $this->database->table('token')->where('token', $newToken)->fetch();
        } while($temp);
        
        $this->database->table('token')->insert(array(
            'token' => $newToken,
            'cd_time' => $this->setCooldown(),
            'previous' => $oldTokenId,
        ));
                
        $this->response->setCookie('gossipToken', $newToken, '100 days');
    }
    
    public function getRemainingCooldown() {
        $token = $this->request->getCookie('gossipToken');
        $row = $this->database->table('token')->where('token', $token)->fetch();
        
        if(!$row){
            $this->generateToken();
            return new \DateInterval('PT1M');
        }
        return $row->cd_time->diff(new \Nette\Utils\DateTime());
    }
    
    private function setCooldown() {
        return date('Y-m-d H:i:s', strtotime('+ 1 minute'));
    }
}