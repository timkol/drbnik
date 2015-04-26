<?php
//$path_here = '/var/www/drbatko/misc/pdf-output/';
$path_there = '/home/www-tex/';

$today              = date("Y-m-d H:i:s", strtotime("today 00:00"));
$yesterday          = date("Y-m-d H:i:s", strtotime("yesterday 00:00"));
//$yesterday = "2014-10-01 00:00:00";
//$today = "2014-10-10 00:00:00";
$int_dayofweek          = date("w", strtotime("yesterday 00:00"));
$sous_begin = strtotime("2015-04-25 00:00:00");
$filename = date("d", strtotime("today 00:00") - $sous_begin);


$dayofweek = null;
switch($int_dayofweek){
case 0:
	$dayofweek = "Neděle";
	break;
case 1:
	$dayofweek = "Pondělí";
	break;
case 2:
	$dayofweek = "Úterý";
	break;
case 3:
	$dayofweek = "Středa";
	break;
case 4:
	$dayofweek = "Čtvrtek";
	break;
case 5:
	$dayofweek = "Pátek";
	break;
case 6:
	$dayofweek = "Sobota";
	break;
}

//echo $today;

//$connection = pripoj();
//$gossip_result = $connection->query("SELECT gossip FROM gossip_details INNER JOIN gossips_check USING(gossip_id) WHERE st_ch_timestamp > '".$yesterday."' AND st_ch_timestamp <= '".$today."' AND status_id = '1'");
////$authors_result = $connection->query("SELECT common_name, COUNT(DISTINCT gossip_id) pocet FROM persons INNER JOIN gossips ON persons.person_id=gossips.author_id INNER JOIN gossip_details USING(gossip_id) INNER JOIN gossips_check USING(gossip_id) WHERE status_id = '1' AND st_ch_timestamp <= '".$today."' GROUP BY persons.person_id ORDER BY pocet DESC, common_name ASC");
////$victims_result = $connection->query("SELECT common_name, COUNT(DISTINCT gossip_id) pocet FROM persons INNER JOIN gossips ON persons.person_id=gossips.victim_id INNER JOIN gossip_details USING(gossip_id) INNER JOIN gossips_check USING(gossip_id) WHERE status_id = '1' AND st_ch_timestamp <= '".$today."' GROUP BY persons.person_id ORDER BY pocet DESC, common_name ASC");
////$victims_result = $connection->query("SELECT common_name, COUNT(DISTINCT gossip_id) pocet FROM persons INNER JOIN gossips ON persons.person_id=gossips.victim_id INNER JOIN gossip_details USING(gossip_id) WHERE status_id = '1' GROUP BY persons.person_id ORDER BY pocet DESC, common_name ASC");
//
//$connection->close();

function compare($a, $b) {
    return $b['count'] - $a['count'];
}

$container = require __DIR__ . '/../bootstrap.php';
/** @var App\Model\GossipManager */
$manager = $container->getByType('App\Model\GossipManager');
$database = $container->getByType('Nette\Database\Context');

$gossips = $manager->getByStatus('approved', $today, $yesterday);

$authors_result = array();
$authors = $database->table('person');
foreach ($authors as $author) {
    $authorName = $manager->getPersonDisplayName($author);
    $count = $manager->getByAuthor($author->person_id, 'approved', $today)->getRowCount();
    if(!$count) {
        continue;
    }
    $authors_result[] = array(
            'name' => $authorName,
            'count' => $count,
        );
}
usort($authors_result, 'compare');

$victims_result = array();
$victims = $database->table('person');
foreach ($authors as $victim) {
    $victimName = $manager->getPersonDisplayName($victim);
    $count = $manager->getByVictim($victim->person_id, 'approved', $today)->getRowCount();
    if(!$count) {
        continue;
    }
    $victims_result[] = array(
            'name' => $victimName,
            'count' => $count,
        );
}
usort($victims_result, 'compare');


//echo "Parsing data\n";

$drby = '\documentclass[12pt, a4paper]{article}'."\n";
$drby .= '\usepackage[utf8]{inputenc}'."\n";
$drby .= '\usepackage[czech]{babel}'."\n";
$drby .= '\usepackage{multicol}'."\n";
$drby .= '\usepackage{a4wide}'."\n";


$drby .= '\begin{document}'."\n";
$drby .= '\begin{Huge}\begin{center}Drby -- '.$dayofweek.'\end{center}\end{Huge}'."\n";

$drby .= '\setlength{\parskip}{3pt}';
$drby .= '\begin{multicols}{2}'."\n\\noindent\n";

//while($row = $gossip_result->fetch_assoc()){
//	$drby .= $row['gossip'].'\\par\\noindent'."\n";
//}

foreach ($gossips as $gossip) {
    $drby .= $gossip->gossip.'\\par\\noindent'."\n";
}

$drby .= '\end{multicols}'."\n";

$drby .= '\end{document}';

//echo $drby;


$autori = '\documentclass[12pt, a4paper]{article}'."\n";
$autori .= '\usepackage[utf8]{inputenc}'."\n";
$autori .= '\usepackage[czech]{babel}'."\n";
//$autori .= '\usepackage{a4wide}'."\n";

$autori .= '\begin{document}'."\n";
$autori .= '\begin{Huge}\begin{center}Statistika -- '.$dayofweek.'\end{center}\end{Huge}'."\n";
$autori .= '\begin{tabular}{|c|c|c| c |c|c|c|}\hline'."\n";
//$autori .= '\cline1--3\cline4--6'."\n";
$autori .= 'Pořadí & Jméno autora & Počet drbů && Pořadí & Jméno drbaného & Počet drbů \\\\'."\n";
$autori .= '\hline'."\n";

$maxCount = max(array(count($authors_result), count($victims_result)));
for($i=0; $i<$maxCount; $i++) {
    $author = $authors_result[$i];
    $victim = $victims_result[$i];
    
    if(!$author && !$victim) { 
	break;
    }
    if($author) {
	$autori .= ($i+1).'. & '.$author['name'].' & '.$author['count']."& &";
    }
    else {
	$autori .= "& & & &";
    }               
    if($victim) {
	$autori .= ($i+1).'. & '.$victim['name'].' & '.$victim['count'].'\\\\'."\n";
    }
    else {
	$autori .= "& &\\\\";
    }
	$autori .= '\hline'."\n";
}
$autori .= '\end{tabular}'."\n";
$autori .= '\end{document}';

file_put_contents($path_there.'drby/drb'.$filename.'.tex', $drby);
file_put_contents($path_there.'statistika-autori/autor-stat'.$filename.'.tex', $autori);

echo $filename;