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
        $this->database->table('team_points')->wherePrimary($id)->update(array(
            'active' => 0
        ));
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
    
    private function redrawPoints() {
        //TODO
    }
}