<?php

namespace App\Model;
use Nette;

class TrialManager extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;

    public function __construct(Nette\Database\Context $database)
    {
	$this->database = $database;
    }
    
    public function confirmTrial($trialId, $studentId) {
        
        $this->database->table('trial_pass')->insert(array(
            'trial_id' => $trialId,
            'student_id' => $studentId
        ));
    }
    
    /**
     * 
     * @param string|null $authorId
     * @return \Nette\Database\Table\ActiveRow
     */
    public function getByAuthor($authorId = null) {
        return $this->database->table('trial')->where('author_id', $authorId);
    }
}