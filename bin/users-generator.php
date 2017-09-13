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
$authTokenTable = new authTokenContainer("../resources/auth_token.sql");
$loginStripCSV = new loginStripContainer("../resources/logins.csv");

if (($handle = fopen("../resources/origin/contestants.csv", "r")) !== FALSE) {
    while (($person = fgetcsv($handle, 1000, ";")) !== FALSE) {
        
        $family_name = $person[1];
        $other_name = $person[0];
        $display_name = $person[2];
        $gender  = $person[3];
        $lang = $person[4];
        $token = $person[5];
        $foto_filename = $person[6];
        
        $login = generateLogin($other_name, $family_name);
        $password = generatePassword();
        $hash = Passwords::hash($password);
        
        //$role_id = 0;
        $person_type = 'pako';
        
        $person_id = $personTable->add($family_name, $other_name, $display_name, $gender, $person_type, $lang, $foto_filename);
        $login_id = $loginTable->add($person_id, $login, $hash);
        if($token !== '0') {
            $auth_token_id = $authTokenTable->add($login_id, $token, 'PHP-PERM');
        }
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
        $lang = $person[4];
        $token = $person[5];
        $foto_filename = $person[6];
        
        $login = generateLogin($other_name, $family_name);
        $password = generatePassword();
        $hash = Passwords::hash($password);
        
        $role_id = 1;
        $person_type = 'org';
        
        $person_id = $personTable->add($family_name, $other_name, $display_name, $gender, $person_type, $lang, $foto_filename);
        $login_id = $loginTable->add($person_id, $login, $hash);
        if($token !== '0') {
            $auth_token_id = $authTokenTable->add($login_id, $token, 'PHP-PERM');
        }
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
    }


    public function add($family_name, $other_name, $display_name, $gender, $person_type, $lang, $foto_filename) {
        $this->last_person_id++;
        $record = "INSERT INTO person (person_id, family_name, other_name, display_name, gender, person_type, lang, foto_filename) VALUES "
                . "($this->last_person_id, '$family_name', '$other_name', '$display_name', '$gender', '$person_type', '$lang', '$foto_filename');";
        $this->writeRecord($record);
        return $this->last_person_id;
    }
}

class loginContainer extends baseContainer {
    private $last_login_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
    }


    public function add($person_id, $login, $hash) {
        $this->last_login_id++;
        $record = "INSERT INTO login (login_id, person_id, login, hash, active) VALUES "
                . "($this->last_login_id, $person_id, '$login', '$hash', 1);";
        $this->writeRecord($record);
        return $this->last_login_id;
    }
}

class authTokenContainer extends baseContainer {
    private $last_auth_token_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
    }


    public function add($login_id, $token, $type) {
        $this->last_auth_token_id++;
        $record = "INSERT INTO auth_token (auth_token_id, login_id, type, token) VALUES "
                . "($this->last_auth_token_id, $login_id, '$type', '$token');";
        $this->writeRecord($record);
        return $this->last_auth_token_id;
    }
}

class grantContainer extends baseContainer {
    private $last_grant_id = 0;
    
    public function __construct($logFile) {
        parent::__construct($logFile);
        
    }


    public function add($login_id, $role_id) {
        $this->last_grant_id++;
        $record = "INSERT INTO `grant` (grant_id, login_id, role_id) VALUES "
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