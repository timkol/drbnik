<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;
use App\Model\Authentication\PasswordAuthenticator;


class PersonFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;
    
    /** @var PasswordAuthenticator */
    private $authenticator;


    public function __construct(User $user, PasswordAuthenticator $authenticator)
    {
	$this->user = $user;
        $this->authenticator = $authenticator;
    }
    
    /**
     * @return Form
     */
    public function createAddPersonForm() {        
        $form = parent::create();
        $form->addText('login', 'Login:')
                ->setRequired('Přihlašovací jméno je povinné.');
        $form->addPassword('password', 'Heslo:')
                ->setRequired('Heslo je povinné.');
        $form->addSubmit('submit', 'Registrovat');        
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = array($this, 'addPersonFormSucceeded');
        return $form;
    }
    
    public function addPersonFormSucceeded(Form $form, $values) {
        if (!$this->user->isAllowed('person', 'add')) {
            $form->getPresenter()->error('Nemáte dostatečná oprávnění pro registraci uživatele.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        try {
            $this->authenticator->add($values->login, $values->password);
        } catch (\Exception $ex) {
            $form->addError($ex->getMessage());
        }
    }
    
    /**
    * @return Form
    */
    public function createEditMyselfForm() {        
        $form = parent::create();
        $form->addText('login', 'Login:')
                ->setRequired('Přihlašovací jméno je povinné.')
                ->setDefaultValue($this->user->getIdentity()->data['login']);        
        $form->addPassword('passwordOld', 'Staré heslo:')
                ->setRequired('Zadejte heslo.');
        $form->addPassword('passwordNew', 'Nové heslo:');
        $form->addPassword('passwordNewRepeat', 'Nové heslo znovu:')
                ->addRule(Form::EQUAL, 'Hesla se musí shodovat.', $form['passwordNew'])
                ->setOmitted();
        $form->addSubmit('submit', 'Odeslat');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->onSuccess[] = array($this, 'editMyselfFormSucceeded');
        return $form;
    }
    
    public function editMyselfFormSucceeded(Form $form, $values) {
        if (!$this->user->isAllowed('person', 'editMyself')) {
            $form->getPresenter()->error('Nejprve se přihlašte.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        try {
            $this->authenticator->editMyself($this->user, $values->passwordOld, $values->login, $values->passwordNew);
        } 
        catch (\App\Model\Authentication\DuplicateNameException $ex) {
            $form->addError($ex->getMessage());
        }
        catch (\Nette\Security\AuthenticationException $ex) {
            $form->addError($ex->getMessage());
        }
    }

}