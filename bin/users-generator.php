<?php
use Nette\Utils\Strings;
use Nette\Utils\Random;
use Nette\Security\Passwords;
use Nette\DI\Container;

/**
 * @var Container $container 
 */
$container = require './bootstrap.php';

$personTable = new personContainer("../resources/person.sql");
$loginTable = new loginContainer("../resources/login.sql");
$grantTable = new grantContainer("../resources/grant.sql");
$loginStripCSV = new loginStripContainer("../resources/logins.csv");

if (($handle = fopen("../resources/origin/contestants.csv", "r")) !== FALSE) {
    while (($person = fgetcsv($handle, 1000, ";")) !== FALSE) {
        
        $family_name = $person[1];
        $other_name = $person[0];
        $display_name = $person[2];
        $gender  = $person[3];
        
        $login = generateLogin($other_name, $family_name);
        $password = generatePassword();
        $hash = Passwords::hash($password);
        
        //$role_id = 0;
        $person_type = 'pako';
        
        $person_id = $personTable->add($family_name, $other_name, $display_name, $gender, $person_type);
        $login_id = $loginTable->add($person_id, $login, $hash);
        //$grantTable->add($login_id, $role_id);
        $loginStripCSV->add($login, $password);
    }
    fclose($handle);
}

if (($handle = fopen("../resources/origin/orgs.csv", "r")) !== FALSE) {
    while (($person = fgetcsv($handle, 1000, ";")) !== FALSE) {
        
        $family_name = $person[1];
        $other_name = $person[0];
        $display_name = $person[2];
        $gender  = $person[3];
        
        $login = generateLogin($other_name, $family_name);
        $password = generatePassword();
        $hash = Passwords::hash($password);
        
        $role_id = 1;
        $person_type = 'org';
        
        $person_id = $personTable->add($family_name, $other_name, $display_name, $gender, $person_type);
        $login_id = $loginTable->add($person_id, $login, $hash);
        $grantTable->add($login_id, $role_id);
        $loginStripCSV->add($login, $password);
    }
    fclose($handle);
}

abstract class baseContainer{
    protected $logFile;
    
    public function __construct($logFile) {
        $this->logFile = $logFile;
        
        $myfile = fopen($this->logFile, "w");
        fclose($myfile);
//        if(!file_exists($this->logFile)) {
//            touch($this->logFile);
//        }
    }
    
//    public function contains($userId) {
//        $file_data = file_get_contents($this->logFile);
//        foreach (explode("\n", $file_data) as $value) {
//
//            list($id) = explode(';', $value);
//            if ($id == $userId) {
//                return true;
//            }
//        }
//        return false;
//    }
    
    protected function writeRecord($record) {
        file_put_contents($this->logFile, "\n" . $record, FILE_APPEND);
    }
}

class personContainer extends baseContainer {
    private $last_person_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
        $record = "DROP TABLE IF EXISTS `person`;
CREATE TABLE `person` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Příjmení (nebo více příjmení oddělených jednou mezerou)',
  `other_name` varchar(255) COLLATE utf8_czech_ci NOT NULL COMMENT 'Křestní jména, von, de atd., oddělená jednou mezerou',
  `display_name` varchar(511) COLLATE utf8_czech_ci DEFAULT NULL COMMENT 'zobrazované jméno, liší-li se od <other_name> <family_name>',
  `gender` enum('M','F') CHARACTER SET utf8 NOT NULL,
  `person_type` enum('pako','org','visit') CHARACTER SET utf8 NOT NULL DEFAULT 'pako',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci COMMENT='řazení: <family_name><other_name>, zobrazení <other_name> <f';
"; //DROP if exists, create, ...
        $this->writeRecord($record);
    }


    public function add($family_name, $other_name, $display_name, $gender, $person_type) {
        $this->last_person_id++;
        $record = "INSERT INTO person (person_id, family_name, other_name, display_name, gender, person_type) VALUES "
                . "($this->last_person_id, '$family_name', '$other_name', '$display_name', '$gender', '$person_type');";
        $this->writeRecord($record);
        return $this->last_person_id;
    }
}

class loginContainer extends baseContainer {
    private $last_login_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
        $record = "DROP TABLE IF EXISTS `login`;
CREATE TABLE `login` (
  `login_id` int(11) NOT NULL AUTO_INCREMENT,
  `person_id` int(11) DEFAULT NULL,
  `login` varchar(255) CHARACTER SET utf8 DEFAULT NULL COMMENT 'Login name',
  `hash` char(60) CHARACTER SET utf8 DEFAULT NULL COMMENT 'sha1(login_id . md5(password)) as hexadecimal',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` datetime DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  PRIMARY KEY (`login_id`),
  UNIQUE KEY `login` (`login`),
  UNIQUE KEY `person_id_UNIQUE` (`person_id`),
  CONSTRAINT `login_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;"; //DROP if exists, create, ...
        $this->writeRecord($record);
    }


    public function add($person_id, $login, $hash) {
        $this->last_login_id++;
        $record = "INSERT INTO login (login_id, person_id, login, hash, active) VALUES "
                . "($this->last_login_id, $person_id, '$login', '$hash', 1);";
        $this->writeRecord($record);
        return $this->last_login_id;
    }
}

class grantContainer extends baseContainer {
    private $last_grant_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
        $record = "DROP TABLE IF EXISTS `grant`;
CREATE TABLE `grant` (
  `grant_id` int(11) NOT NULL AUTO_INCREMENT,
  `login_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  PRIMARY KEY (`grant_id`),
  UNIQUE KEY `grant_UNIQUE` (`role_id`,`login_id`),
  KEY `login_id` (`login_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `grant_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE,
  CONSTRAINT `grant_ibfk_3` FOREIGN KEY (`login_id`) REFERENCES `login` (`login_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;"; //DROP if exists, create, ...
        $this->writeRecord($record);
    }


    public function add($login_id, $role_id) {
        $this->last_grant_id++;
        $record = "INSERT INTO grant (grant_id, login_id, role_id) VALUES "
                . "($this->last_grant_id, $login_id, $role_id);";
        $this->writeRecord($record);
        return $this->last_grant_id;
    }
}

class loginStripContainer extends baseContainer {
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
//        $record = "\documentclass[10pt]{article} \n"
//                . "\usepackage[utf8]{inputenc} \n"
//                . "\usepackage[czech]{babel} \n"
//                . "\begin{document} \n"
//                . "\begin{tabular}{"; //DROP if exists, create, ...
//        $this->writeRecord($record);
    }

    public function add($login, $password) {
        $record = "$login;$password";
        $this->writeRecord($record);
    }
}

function generateLogin($name, $surname) {
    $temp = $surname.Strings::substring($name, 0, 1);
    return Strings::webalize($temp);
}

function generatePassword() {
    $pass = '';
    for($i=0; $i<4; $i++) {
        $pass .= generateSou();
        $pass .= generateSamo();
    }
    return $pass;
}

function generateSamo(){
    return Random::generate(1, 'aeiyou');
}

function generateSou(){
    return Random::generate(1, 'bcdfghjklmnprstvz');
}