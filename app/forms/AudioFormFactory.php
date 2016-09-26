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
                //->setRequired();
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
        
        $this->manager->addAudio($values->file);
    }
    
    public function createAddCronTabForm() {        
        $form = parent::create();
        
        $items = $this->manager->listAudio();
        
        $form->addText('day', 'Den:')
                ->addRule(Form::INTEGER, 'Den musí být číslo.')
                ->addRule(Form::RANGE, 'Den musí být v rozsahu od %d do %d.', [1,31])
                ->setRequired();
        $form->addText('hour', 'Hodina:')
                ->addRule(Form::INTEGER, 'Hodina musí být číslo.')
                ->addRule(Form::RANGE, 'Hodina musí být v rozsahu od %d do %d', [0,23])
                ->setRequired();
        $form->addText('min', 'Minuta:')
                ->addRule(Form::INTEGER, 'Minuta musí být číslo.')
                ->addRule(Form::RANGE, 'Minuta musí být v rozsahu od %d do %d.', [0,59])
                ->setRequired();
        $form->addText('rep', 'Počet opakování:')
                ->addRule(Form::INTEGER, 'Počet opakování musí být číslo.')
                ->addRule(Form::MIN, 'Počet opakování musí být kladný.', 1)                
                ->setRequired();
        $form->addSelect('file', 'Soubor k přehrání:')
                ->setItems($items, false)
                ->setRequired();
        $form->addSubmit('submit', 'Odeslat');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = array($this, 'addCronTabFormSucceeded');
        return $form;
    }
        
    public function addCronTabFormSucceeded(Form $form, $values)
    {
        if (!$this->user->isAllowed('audio', 'addCron')) {
            $form->getPresenter()->error('Nemáte oprávnění pro přidání audio souboru.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        $this->manager->addCronTab($values->day, $values->hour, $values->min, $values->file, $values->rep);
    }
}