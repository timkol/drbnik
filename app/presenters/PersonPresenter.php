<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\Authentication\PasswordAuthenticator;
use App\Forms\PersonFormFactory;
use Nette\Database\Context;


/**
 * Homepage presenter.
 */
class PersonPresenter extends BasePresenter
{
    /** @var Model\Authentication\PasswordAuthenticator */
    private $authenticator;
    
    /** @var PersonFormFactory @inject */
    public $factory;
    
    public function __construct(PasswordAuthenticator $authenticator) {
        parent::__construct();
        $this->authenticator = $authenticator;
    }
    
    public function actionEditMyself()
    {
        if (!$this->getUser()->isAllowed('person', 'editMyself')) {
            $this->redirect('Sign:in');
        }
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