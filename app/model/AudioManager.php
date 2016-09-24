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
    
    public function addCronTab( $day, $hour, $min, $file, $rep )
    {
        $audioList = $this->readCronTab( );
        $audioList[] = array(
            "day"  => $day,
            "hour" => $hour,
            "min"  => $min,
            "file" => $file,
            "rep"  => $rep,
            "user" => null
        );
        writeLineCronTab( $audioList );
    }

    public function readCronTab( )
    {
        $audioList = array();
        exec( "crontab -l > " . $this->cronFileName );
        $cronFile = fopen( $this->cronFileName, "r" );

        $line = fgets( $cronFile );
        $line = fgets( $cronFile );

        while( !feof( $cronFile ) ) {
            $line = fgets( $cronFile );
            $rawData = explode( " ", $line );
            if( count( $rawData ) != 11)
                break;
            $audioList[] = array(
                "day"  => $rawData[4],
                "hour" => $rawData[1],
                "min"  => $rawData[0],
                "file" => $rawData[8],
                "rep"  => $rawData[7],
                "user" => $rawData[10]
            );
        }
        fclose( $cronFile );

        return $audioList;
    }

    public function readFutureCronTab( )
    {
        $futureAudioList = array( );

        $audioList = $this->readCronTab( );
        $dateTime = new \DateTime( );
        $dates = explode( " ", $dateTime->format( "w G i" ) );
        $day  = $dates[0] % 7;
        $hour = $dates[1];
        $min  = $dates[2];
        foreach( $audioList as $audio ) {
            if( $day > $audio["day"] )
                continue;
            if( $day ==  $audio["day"] ) {
                if( $hour > $audio["hour"] )
                    continue;
                if( $hour == $audio["hour"] ) {
                    if( $min > $audio["min"] )
                        continue;
                }
            }
            $futureAudioList[] = $audio;
        }
        return $futureAudioList;
    }

    public function writeCronTab( $audioList )
    {
        $cronFile = fopen( $this->cronFileName, "w" );
        fwrite( $cronFile, "SHELL=/bin/sh\n" );
        fwrite( $cronFile, "PATH=/usr/bin\n" );

        foreach( $audioList as $audio )
            $this->writeLineCronTab( $cronFile,
                $audio["day"], $audio["hour"], $audio["min"],
                $audio["file"], $audio["rep"], $audio["user"] );

        fclose( $cronFile );
        exec( "cat " . $this->cronFileName . " | crontab -" );
    }

    private function writeLineCronTab( $cronFile, $day, $hour, $min, $file, $rep, $user = null )
    {
        if( is_null( $user ) )
            $user = $this->user->id;
        if( $user == "" )
            $user = "fantomas";
        fwrite( $cronFile,
            $min . " " . $hour . " * * " . $day . " cvlc --input-repeat " . $rep . " " . $file . " # " . $user . "\n" );
    }

    public function stopPlay( )
    {
        exec( 'killall vlc' );
    }
}
