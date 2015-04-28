<?php
use Nette\Utils\Strings;

//$path_here = '/var/www/drbatko/misc/pdf-output/';
$path_there = '/home/oracle/opengl/';

$today              = date("Y-m-d H:i:s", strtotime("today 00:00"));
$yesterday          = date("Y-m-d H:i:s", strtotime("yesterday 00:00"));
//$yesterday = "2014-10-01 00:00:00";
//$today = "2014-10-10 00:00:00";
$int_dayofweek          = date("w", strtotime("yesterday 00:00"));
$sous_begin = strtotime("2015-04-25 00:00:00");
$filename = date("d", strtotime("today 00:00") - $sous_begin);

$container = require __DIR__ . '/bootstrap.php';
/** @var App\Model\GossipManager */
$manager = $container->getByType('App\Model\GossipManager');
$database = $container->getByType('Nette\Database\Context');

$gossips = $manager->getByStatus('approved', $today, $yesterday);

$payload = '#ifndef GOSSPIS_H'."\n";
$payload .= '#define GOSSPIS_H'."\n";
$payload .= '#include <string>'."\n";

$payload .= 'int gossipsCount = '.count($gossips).';'."\n";

$payload .= 'std::string gossips[] ={'."\n";

foreach ($gossips as $gossip) {
    $payload .= '"'. Strings::toAscii($gossip->gossip).'",'."\n";
}
$payload .= '};'."\n";


$payload .= '#endif'."\n";


file_put_contents($path_there.'gossips.h', $payload);