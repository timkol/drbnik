<?php

namespace App\Model;
use Nette;

class FeedbackManager extends Nette\Object {
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
     */
    public function add($feedback, $authors) {        
        $gossipInsert = $this->database->table('feedback')->insert(array(
            'feedback' => $feedback
        ));
        
        foreach ($authors as $author) {
            $this->database->table('feedback_author')->insert(array(
                'feedback_id' => $gossipInsert->feedback_id,
                'author_id' => $author
            ));
        }
    }
    
    public function getAll() {
        return $this->database->table('feedback');
    }
}
