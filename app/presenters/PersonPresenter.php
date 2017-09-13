<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\Authentication\PasswordAuthenticator;
use App\Forms\PersonFormFactory;
use Nette\Database\Context;
use App\Model\GossipManager;


/**
 * Homepage presenter.
 */
class PersonPresenter extends BasePresenter
{
    /** @var Model\Authentication\PasswordAuthenticator */
    private $authenticator;
    
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var GossipManager */
    private $gossipManager;
    
    /** @var PersonFormFactory @inject */
    public $factory;
    
    public function __construct(PasswordAuthenticator $authenticator, Context $database, GossipManager $gossipManager) {
        parent::__construct();
        $this->authenticator = $authenticator;
        $this->database = $database;
        $this->gossipManager = $gossipManager;
    }
    
    public function actionEditMyself()
    {
        if (!$this->getUser()->isAllowed('person', 'editMyself')) {
            $this->redirect('Sign:in');
        }
    }
    
    public function renderShow($id) {
        if (!$this->getUser()->isAllowed('person', 'show')) {
            $this->error('Nemáte dostatečná oprávnění pro zobrazení detailu člověka.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $person = $this->database->table('person')->get($id);
        if(!$person) {
            $this->error("Uživatel $id neexistuje.");
        }
        $this->template->fotoPath = $this->context->parameters['foto']['fotoPath'];
        $this->template->person = $person;
        $this->template->gossips = $this->gossipManager->getByVictim($id, 'approved');
    }
    
    public function renderList() {
        if (!$this->getUser()->isAllowed('person', 'show')) {
            $this->error('Nemáte dostatečná oprávnění pro zobrazení lidí.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->template->persons = $this->database->table('person');
        $this->template->fotoPath = $this->context->parameters['foto']['fotoPath'];
    }
    
    protected function createComponentAddPersonForm() {
        $form = $this->factory->createAddPersonForm();
	$form->onSuccess[] = function ($form, $values) {            
            $form->getPresenter()->flashMessage('Uživatel '.$values->login.' byl registrován.', 'success');
            $form->getPresenter()->redirect('Person:add');
	};
	return $form;
    }
    
    protected function createComponentEditMyselfForm() {
        $form = $this->factory->createEditMyselfForm();
	$form->onSuccess[] = function ($form) {
            $form->getPresenter()->flashMessage('Údaje byly změněny.', 'success');
            $form->getPresenter()->redirect('Gossip:');
	};
	return $form;
    }
}