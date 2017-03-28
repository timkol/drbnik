<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\GossipToken\GossipToken;
use App\Forms\TeamPointsFormFactory;
use App\Model\GossipManager;
use Nette\Application\Responses\JsonResponse;
use App\Model\AnimatedGossip\AnimatedGossipFactory;
use App\Model\TeamPointsManager;

class TeamPointsPresenter extends BasePresenter
{
    /** @var TeamPointsFormFactory @inject */
    public $factory;
    
    /** @var TeamPointsManager @inject */
    public $manager;
    
    public function actionAdd() {
        if (!$this->getUser()->isAllowed('teamPoints', 'add')) {
            $this->error('Nemáte oprávnění ke změně bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    public function actionRefresh() {
        //if($this->getHttpRequest()->getRemoteAddress() !== '127.0.0.1') {
        //    $this->error('Nemáte oprávnění k refreshi bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        //}
        $this->manager->redrawPoints();
        $this->sendJson([]);
    }
    
    public function actionLocalAdd() {
        if (!$this->getUser()->isAllowed('teamPoints', 'add')) {
            $this->error('Nemáte oprávnění ke změně bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $team = $this->request->getPost('team');
        $pointsChange = $this->request->getPost('pointsChange');
        $note = $this->request->getPost('note');
        
        $teamId = $this->database->table('team')->where('name', $team)->team_id;
        
        $this->manager->add($teamId, $pointsChange, $note);
        $this->user->logout(true);
        $this->sendJson([]);
    }

    public function actionDelete($id) {
        if (!$this->getUser()->isAllowed('teamPoints', 'delete')) {
            $this->error('Nemáte oprávnění ke změně bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->manager->delete($id);
        $this->flashMessage('Smazání proběhlo úspěšně', 'success');
        $this->redirect("list");
    }

    public function actionList() {
        if (!$this->getUser()->isAllowed('teamPoints', 'listChanges')) {
            $this->error('Nemáte oprávnění k prohlížení změn bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->template->pointChanges = $this->manager->getAll();
    }
    
    public function actionShow() {
        if (!$this->getUser()->isAllowed('teamPoints', 'show')) {
            $this->error('Nemáte oprávnění k prohlížení bodů.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->template->points = $this->manager->getCurrent();
    }
    
    protected function createComponentAddForm() {
        $form = $this->factory->createAddForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Úprava bodů proběhla úspěšně.', 'success');
            $form->getPresenter()->redirect('TeamPoints:add');
	};
	return $form;
    }
}