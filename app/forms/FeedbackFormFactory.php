<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;
use App\Model\FeedbackManager;
use App\Model\GossipManager;
use Nette\Database\Context;


class FeedbackFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;
    
    /** @var FeedbackManager */
    private $manager;
    
    /** @var GossipManager */
    private $gossipManager;
    
    /** @var Context */
    private $database;

    public function __construct(User $user, FeedbackManager $manager, GossipManager $gossipManager, Context $database)
    {
	$this->user = $user;
        $this->manager = $manager;
        $this->database = $database;
        $this->gossipManager = $gossipManager;
    }
    
    public function createFeedbackForm() {
        $persons = $this->createPersonList();
        
        $form = parent::create();
        $form->addMultiSelect('authors', 'Autoři:', $persons)
                ->setRequired('Musí být vyplněn alespoň jeden autor.')
                ->setDefaultValue($this->getLoggedPersonId())
                ->addRule(array($this, 'loggedAuthorFilledValidator'), 'Přihlášený člověk musí být autorem.', $this->getLoggedPersonId())
                ->setAttribute('class','authors');
        $form->addTextArea('feedback', 'Text stížnosti:')
                ->addRule(Form::MAX_LENGTH, 'Text zhodnocení pracoviště smí být dlouhý maximálně %d znaků.', 65535)
                ->setRequired('Text zhodnocení pracoviště nesmí být prázdný')
                ->setAttribute('placeholder', 'Zde napiš text zhodnocení pracoviště.');
        $form->addSubmit('submit', 'Odeslat');
        $form->addButton('null','původní hodnoty')
                ->setAttribute('type', 'reset')
                ->setAttribute('class','reset');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = array($this, 'feedbackFormSucceeded');
        return $form;
    }
    
    public function loggedAuthorFilledValidator($item, $arg) {
        if($arg === null){
            return true;
        }
        return in_array($arg, $item->value);
    }
        
    public function feedbackFormSucceeded(Form $form, $values)
    {
        if (!$this->user->isAllowed('feedback', 'add')) {
            $form->getPresenter()->error('Nemáte oprávnění pro přidání zhodnocení pracoviště.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        $this->manager->add($values->gossip, $values->authors);
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