<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;
use App\Model\GossipToken\GossipToken;
use App\Model\TrialManager;
use Nette\Database\Context;


class TrialFormFactory extends BaseFormFactory
{
    /** @var User */
    private $user;
    
    /** @var TrialManager */
    private $manager;
    
    /** @var Context */
    private $database;

    public function __construct(User $user, TrialManager $manager, Context $database)
    {
	$this->user = $user;
        $this->manager = $manager;
        $this->database = $database;
    }
    
    public function createConfirmForm() {
        $form = parent::create();
        
        $trials = $this->manager->getByAuthor($this->user->id)->fetchAll();
        $students = $this->database->table('person')->where('person_type', 'pako');
        foreach($trials as $trial) {
            $form->addGroup($trial->name);
            $sub = $form->addContainer($trial->trial_id);
            foreach($students as $student) {
                $studentPassed = $this->database->table('trial_pass')->where('student_id', $student->person_id)->where('trial_id', $trial->trial_id)->fetch();
                if($studentPassed){
                    continue;
                }
                $sub->addCheckbox($student->person_id, $student->display_name);
            }
        }
        $form->addProtection('Vypršel časový limit, odešlete formulář znovu.');
        $form->addSubmit('submit', 'Odeslat');
        $form->onSuccess[] = array($this, 'confirmFormSucceeded');
        return $form;
    }
    
    public function confirmFormSucceeded(Form $form, $values) {
        if (!$this->user->isAllowed('trial', 'confirm')) {
            $form->getPresenter()->error('Nemáte oprávnění pro schvalování zkoušek.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        foreach($values as $trialId => $trial) {
            foreach($trial as $studentId => $passed) {
                if($passed) {
                    $this->manager->confirmTrial($trialId, $studentId);
                }
            }
        }
    }
}