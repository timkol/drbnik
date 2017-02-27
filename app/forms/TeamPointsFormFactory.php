<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;
use App\Model\TeamPointsManager;
use Nette\Database\Context;


class TeamPointsFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;
    
    /** @var TeamPointsManager */
    private $manager;
    
    /** @var Context */
    private $database;

    public function __construct(User $user, TeamPointsManager $manager, Context $database)
    {
	$this->user = $user;
        $this->manager = $manager;
        $this->database = $database;
    }
    
    public function createAddForm() {
        $form = parent::create();
        
        $teams = $this->database->table('team')->fetchPairs('team_id', 'name');
        
        $form->addSelect('team', 'Tým:', $teams)
                ->setPrompt('Zadejte tým')
                ->setRequired();
        $form->addRadioList('sign', '', ['+' => 'uděluji', '-' => 'odebírám'])
                ->setRequired();
        $form->addText('change', 'Body:')
                ->setType('number')
                ->addRule(Form::INTEGER, 'Body musí být celé číslo.')
                ->addRule(Form::MIN, 'Body musí být kladné', 0)
                ->setRequired();
        $form->addText('note', 'Poznámka:');
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = array($this, 'addFormSucceeded');
        return $form;
    }
    
    public function addFormSucceeded(Form $form, $values) {
        if (!$this->user->isAllowed('teamPoints', 'add')) {
            $form->getPresenter()->error('Nemáte oprávnění ke změně bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        $pointsChange = (($values['sign'] == '+')? 1 : -1) * $values['change'];
        $this->manager->add($values['team'], $pointsChange, $values['note']);
    }
}