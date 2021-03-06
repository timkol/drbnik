<?php

namespace App\Model;
use Nette;

class TeamPointsManager extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var \Nette\Security\User */
    private $user;

    public function __construct(Nette\Database\Context $database, \Nette\Security\User $user)
    {
	$this->database = $database;
        $this->user = $user;
    }
    
    public function add($teamId, $pointsChange, $note = null) {        
        $this->database->table('team_points')->insert(array(
            'team_id' => $teamId,
            'org_id' => $this->user->id,
            'points_change' => $pointsChange,
            'note' => $note
        ));
        $this->redrawPoints();
    }
    
    public function delete($id) {
        if($this->database->table('team_points')->get($id)->active > 1) {
            return;
        }
        $this->database->table('team_points')->wherePrimary($id)->update(array(
            'active' => 0
        ));
        $this->redrawPoints();
    }

    /**
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getAll() {
        return $this->database->table('team_points');
    }
    
    /**
     * 
     * @return \Nette\Database\Table\Selection
     */
    public function getCurrent() {
        return $this->database->table('v_team_points');
    }
    
    public function redrawPoints() {
        //TODO
        //$fp = stream_socket_client("tcp://www.example.com:80", $errno, $errstr, 30);
        //fwrite($fp, "GET / HTTP/1.0\r\nHost: www.example.com\r\nAccept: */*\r\n\r\n");
        $points = $this->getCurrent()->fetchPairs('team', 'points');
        $gryffindor = $points['Nebelvír'];
        $hufflepuff = $points['Mrzimor'];
        $ravenclaw = $points['Havraspár'];
        $slytherin = $points['Zmijozel'];
        file_get_contents("http://localhost:3000/points?gryffindor=".$gryffindor."&hufflepuff=".$hufflepuff."&ravenclaw=".$ravenclaw."&slytherin=".$slytherin);
    }
}