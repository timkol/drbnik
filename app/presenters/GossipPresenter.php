<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\GossipToken\GossipToken;
use App\Forms\GossipFormFactory;
use App\Model\GossipManager;
use Nette\Application\Responses\JsonResponse;
use App\Model\AnimatedGossip\AnimatedGossipFactory;
use App\Model\Authentication\TokenAuthenticator;

/**
 * Gossip presenter.
 */
class GossipPresenter extends BasePresenter
{
    /** @var GossipFormFactory @inject */
    public $factory;
    
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var GossipToken */
    private $token;
    
    /** @var GossipManager */
    private $model;
    
    /** @var  Nette\Http\Request */
    private $httpRequest;
    
    /** @var AnimatedGossipFactory @inject */
    public $aniGossFactory;
    
    /** @var TokenAuthenticator @inject */
    public $tokenAuthenticator;

    public function __construct(Nette\Database\Context $database, GossipToken $token, GossipManager $model, Nette\Http\Request $httpRequest)
    {
        parent::__construct();
        $this->database = $database;
        $this->token = $token;
        $this->model = $model;
        $this->httpRequest = $httpRequest;
    }    

//    public function renderDefault()
//    {
//        $this->template->remainingCD = $this->token->getRemainingCooldown()->format('%R %i:%s');
//    }
    
    public function renderList(){
        $this->template->gossips = array(
            'new' => $this->model->getByStatus('new')->fetchAll(),
            'approved' => $this->model->getByStatus('approved')->fetchAll(),
            'rejected' => $this->model->getByStatus('rejected')->fetchAll()
        );
    }

    public function actionDefault() {
        if (!$this->getUser()->isAllowed('gossip', 'displayForm')) {
            $this->redirect('Sign:in');
        }
    }
    
    public function actionList() {
        if (!$this->getUser()->isAllowed('gossip', 'show')) {
            $this->redirect('Sign:in');
        }
    }

    public function actionApprove(){
        if (!$this->getUser()->isAllowed('gossip', 'approve')) {
            $this->error('Nemáte oprávnění ke schvalování nesprávného chování na pracovišti.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    protected function createComponentApproveForm() {
        $form = $this->factory->createApproveForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Schválení proběhlo úspěšně.', 'success');
            $form->getPresenter()->redirect('Gossip:approve');
	};
	return $form;
    }
    
    protected function createComponentGossipForm() {
        $form = $this->factory->createGossipForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Vedení společnosti Vám děkuje.', 'success');
            $form->getPresenter()->redirect('Gossip:');
	};
	return $form;
    }
    
    public function actionAjax() {
        if (!$this->getUser()->isAllowed('gossip', 'show')) {
            $this->error('Nemáte oprávnění k prohlížení nesprávného chování na pracovišti.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $previousId = $this->httpRequest->getQuery('id');
        $new_drb = $this->aniGossFactory->create($previousId);
        $request = '<div class="drb">' . $new_drb->getParsed() . '</div>';
        $id = $new_drb->getId();
        if($this->isAjax()) {
            $this->sendResponse(new JsonResponse(array('html' => $request, 'id' => $id)));
        }
    }
    
    public function actionDownload($timestampMax, $timestampMin) {
        if(!$this->getUser()->isLoggedIn()){
            $key = $this->getHttpRequest()->getQuery("fksis-key");
            $this->tokenAuthenticator->login($key);
        }
        
        $timestampMax = is_numeric($timestampMax)?$timestampMax:time();
        $timestampMin = is_numeric($timestampMin)?$timestampMin:1;
        
        $datetimeMax = date('Y-m-d H:i:s', $timestampMax);
        $datetimeMin = date('Y-m-d H:i:s', $timestampMin);
        
        if (!$this->getUser()->isAllowed('gossip', 'show')) {
            $this->error('Nemáte oprávnění k prohlížení nesprávného chování na pracovišti.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $gossips = $this->model->getByStatus('approved', $datetimeMax, $datetimeMin)->fetchAll();
        $gossipArr = array();
        foreach($gossips as $gossip){
            $gossipArr[] = $gossip['gossip'];
        }
        $this->sendResponse(new JsonResponse($gossipArr, 'application/json; charset=utf-8'));
    }
    
    public function renderProphet() {
        $this->template->host = $this->getHttpRequest()->getUrl()->host;
    }

    public function actionAdd() {
        if (!$this->getUser()->isAllowed('gossip', 'add')) {
            $this->error('Nemáte oprávnění k přidání drbu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        
        $gossip = $this->request->getPost('gossip');
        $person = $this->database->table('person')->where(':login.login_id', $this->user->id)->fetch();
        $personId = ($person) ? $person->person_id : null;
        
        $this->model->add($gossip, [$personId], []);
        //$this->getHttpRequest()->getUrl()->host;
        $this->user->logout(true);
        $this->redirect("Sign:in");
    }

}