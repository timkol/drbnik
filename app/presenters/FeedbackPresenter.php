<?php

namespace App\Presenters;

use Nette,
	App\Model;
use Nette\Application\UI;
use App\Model\GossipToken\GossipToken;
use App\Forms\FeedbackFormFactory;
use App\Model\FeedbackManager;
use Nette\Application\Responses\JsonResponse;
use App\Model\AnimatedGossip\AnimatedGossipFactory;

/**
 * Gossip presenter.
 */
class FeedbackPresenter extends BasePresenter
{
    /** @var FeedbackFormFactory @inject */
    public $factory;
    
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var FeedbackManager */
    private $model;
    
    /** @var  Nette\Http\Request */
    private $httpRequest;
    
    /** @var AnimatedGossipFactory @inject */
    public $aniGossFactory;

    public function __construct(Nette\Database\Context $database, FeedbackManager $model, Nette\Http\Request $httpRequest)
    {
        parent::__construct();
        $this->database = $database;
        $this->model = $model;
        $this->httpRequest = $httpRequest;
    }
    
    public function actionDefault() {
        if (!$this->getUser()->isAllowed('feedback', 'displayForm')) {
            $this->redirect('Sign:in');
        }
    }
    
    protected function createComponentFeedbackForm() {
        $form = $this->factory->createFeedbackForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Vedení společnosti Vám děkuje.', 'success');
            $form->getPresenter()->redirect('Feedback:');
	};
	return $form;
    }

}