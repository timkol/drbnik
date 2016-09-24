<?php

namespace Test;

use Nette,
	Tester,
	Tester\Assert;

$container = require __DIR__ . '/bootstrap.php';


class LukasTest extends Tester\TestCase
{
	private $container;
    private $audio;


	function __construct( Nette\DI\Container $container )
	{
		$this->container = $container;
        $this->audio     = $container->getByType( "App\Model\AudioManager" );
	}


	function setUp()
	{
	}


	function testSomething()
	{
		Assert::true( true );
	}

    function testRun( )
	{
        $audioList = array(
                array(
                    "day"  => 1,
                    "hour" => 2,
                    "min"  => 3,
                    "file" => "xxx.mp3",
                    "rep"  => 10,
                    "user" => null
                    )
                );

        $this->audio->writeCronTab( $audioList );
        $this->audio->readCronTab( );
        $this->audio->readFutureCronTab( );
	}

}


$test = new LukasTest($container);
$test->run();
