<?php

namespace App\Model;
use Nette;

class AudioManager extends Nette\Object {
    
    /** @var Nette\Security\User */
    private $user;
    
    /** @var Nette\DI\Container */
    private $context;
    
    private $cronFileName = "/tmp/crontabs-fks-audio";
    private $audioDir     = "";


    public function __construct(Nette\Security\User $user, Nette\DI\Container $context)
    {
        $this->user = $user;
        $this->context = $context;
        //$basePath = $this->context->parameters['audio']['basePath'];
    }
    
    public function addCronTab( $day, $hour, $min, $file, $rep )
    {
        $audioList = $this->readCronTab( );
        $audioList[] = array(
            "day"  => $day,
            "hour" => $hour,
            "min"  => $min,
            "file" => $this->audioDir . "/" . $file,
            "rep"  => $rep,
            "user" => null
        );
        $this->writeCronTab( $audioList );
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
                continue;
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

        fwrite( $cronFile, "\n" );
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
            $min . " " . $hour . " * * " . $day . " cvlc --input-repeat " . $rep . " " . $file . " # " . $user );
    }

    public function stopPlayAudio( )
    {
        exec( 'killall vlc' );
    }

    public function playAudio( $filename, $rep )
    {
        if( Strings::webalize( $filename ) !== $file )
            return;
        exec( "cvlc --input-repeat " . $rep . " " . $audioDir . "/" . $filename );
    }

    public function listAudio( )
    {
        return array_diff( scandir( $this->audioDir ), array('..', '.') );
    }

    public function addAudio( $file )
    {
    }

    public function deleteAudio( $filename )
    {
        if( Strings::webalize( $filename ) !== $file )
            return;
        exec( "rm " . $this->audioDir . "/" . $filename );
    }
}
