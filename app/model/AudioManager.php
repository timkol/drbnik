<?php

namespace App\Model;
use Nette;

class AudioManager extends Nette\Object {
    
    /** @var Nette\Security\User */
    private $user;
    private $cronFileName = "/tmp/crontabs-fks-audio";


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

    #private function writeCronTab( )
    public function writeCronTab( $audioList )
    {
        $cronFile = fopen( $this->cronFileName, "w" );
        fwrite( $cronFile, "SHELL=/bin/sh\n" );
        fwrite( $cronFile, "PATH=/usr/bin\n" );

        foreach( $audioList as $audio)
            $this->writeLineCronTab( $cronFile,
                $audio["day"], $audio["hour"], $audio["min"],
                $audio["file"], $audio["rep"] );

        fclose( $cronFile );
        exec( "cat " . $this->cronFileName . " | crontab -" );
    }

    private function writeLineCronTab( $cronFile, $day, $hour, $min, $file, $rep )
    {
        fwrite( $cronFile,
            $min . " " . $hour . " * * " . $day . " cvlc --input-repeat " . $rep . " " . $file . " # " . $this->user->id . "\n" );
    }

    public function stopPlay( )
    {
        exec( 'killall vlc' );
    }
}
