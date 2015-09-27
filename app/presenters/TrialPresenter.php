<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\GossipToken\GossipToken;
use App\Forms\TrialFormFactory;
use App\Model\GossipManager;
use Nette\Application\Responses\JsonResponse;
use App\Model\AnimatedGossip\AnimatedGossipFactory;

/**
 * Trial presenter.
 */
class TrialPresenter extends BasePresenter
{
    /** @var TrialFormFactory @inject */
    public $factory;
    
    public function actionConfirm() {
        if (!$this->getUser()->isAllowed('trial', 'confirm')) {
            $this->error('Nemáte oprávnění ke schvalování zkoušek.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    protected function createComponentConfirmForm() {
        $form = $this->factory->createConfirmForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Schválení proběhlo úspěšně.', 'success');
            $form->getPresenter()->redirect('Trial:confirm');
	};
	return $form;
    }
}