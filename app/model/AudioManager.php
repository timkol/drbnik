<?php

namespace App\Model;
use Nette;

class AudioManager extends Nette\Object {
    
    /** @var Nette\Security\User */
    private $user;


    public function __construct(Nette\Security\User $user)
    {
        $this->user = $user;
    }
    
    /**
     * 
     * @param string $feedback
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
}