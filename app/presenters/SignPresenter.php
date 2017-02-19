<?php

namespace App\Presenters;

use Nette,
	App\Forms\SignFormFactory;
use App\Model\Authentication\TokenAuthenticator;
use Nette\Application\Responses\JsonResponse;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
	/** @var SignFormFactory @inject */
	public $factory;

        /** @var TokenAuthenticator @inject */
        public $tokenAuthenticator;

	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = $this->factory->create();
		$form->onSuccess[] = function ($form) {
			$form->getPresenter()->redirect('Gossip:');
		};
		return $form;
	}
        
        /**
         * Used for token authentication by user
         */
        public function actionIn($token = null) {
            if($token) {
                $this->tokenAuthenticator->login($token);
                $this->redirect('Gossip:');
            }
        }

        public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}
        
        /**
         * Used by programs launching browser with user authenticated
         * Scenario: intermediate program signs in with user permanent token and obtains user temporary token
         * temporary token then "can" be sent in GET when launching browser
         */
        public function actionIntermediate() {
            $permanentToken = $this->request->getPost('token');
            $this->tokenAuthenticator->login($permanentToken);
            $temporaryToken = $this->tokenAuthenticator->addToken(TokenAuthenticator::TYPE_PHP);
            $lang = 'cs-CZ';//TODO z DB
            $this->sendResponse(new JsonResponse(['temporary-token' => $temporaryToken, 'lang' => $lang], 'application/json; charset=utf-8'));
        }

}
