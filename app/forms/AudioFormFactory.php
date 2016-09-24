<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;
use App\Model\AudioManager;
use Nette\Database\Context;


class AudioFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;
    
    /** @var AudioManager */
    private $manager;
    
    /** @var Context */
    private $database;

    public function __construct(User $user, AudioManager $manager, Context $database)
    {
	$this->user = $user;
        $this->manager = $manager;
        $this->database = $database;
    }
    
    public function createAddFileForm() {        
        $form = parent::create();
        $form->addUpload('file', 'Audio soubor:');
        $form->addSubmit('submit', 'Odeslat');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = array($this, 'addFileFormSucceeded');
        return $form;
    }
        
    public function addFileFormSucceeded(Form $form, $values)
    {
        if (!$this->user->isAllowed('audio', 'add')) {
            $form->getPresenter()->error('Nemáte oprávnění pro přidání audio souboru.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        $this->manager->add($values->feedback, $values->authors);
    }
    
    private function createPersonList() {
        $types = array(
            'pako' => 'Zaměstnanci',
            'org' => 'Vedení',
            'visit' => 'Zákazníci',
        );
        $persons = array();
        
        foreach ($types as $personType => $personGroup) {
            $persQuery = $this->database->table('person')->where('person_type', $personType)->order('display_name');
            $pers = array();
            foreach ($persQuery as $person) {
                
//                $name = $person->display_name;
//                if($name === null) {
//                    $name = $person->other_name .' '. $person->family_name;
//                }
                $pers[$person->person_id] = $this->gossipManager->getPersonDisplayName($person);
            }
            $persons[$personGroup] = $pers;
        }        
        return $persons;
    }
    
    private function getLoggedPersonId() {
        $person = $this->database->table('person')->where(':login.login_id', $this->user->id)->fetch();
        if(!$person) {
            return null;
        }
        else {
            return $person->person_id;
        }
    }
}