<?php

namespace App\Presenters;

use Nette,
    App\Model\AudioManager;
use App\Forms\AudioFormFactory;

/**
 * Gossip presenter.
 */
class AudioPresenter extends BasePresenter
{
    
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var AudioManager */
    private $model;
    
    /** @var AudioFormFactory */
    private $factory;
    
    public function __construct(Nette\Database\Context $database, AudioManager $model, AudioFormFactory $factory)
    {
        parent::__construct();
        $this->database = $database;
        $this->model = $model;
        $this->factory = $factory;
    }
    
    public function renderDefault() {
            $this->template->files = $this->model->list();
    }

    protected function createComponentAddForm() {
        $form = $this->factory->createAddFileForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Přidání proběhlo úspěšně.', 'success');
            $form->getPresenter()->redirect('Audio:default');
	};
	return $form;
    }
    
    public function actionDelete($filename){
        if (!$this->getUser()->isAllowed('audio', 'delete')) {
            $this->error('Nemáte oprávnění k odstranění audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->delete($filename);
    }
    
    public function actionPlay($filename){
        if (!$this->getUser()->isAllowed('audio', 'play')) {
            $this->error('Nemáte oprávnění k přehrátí audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->play($filename);
    }
    
    public function actionStop(){
        if (!$this->getUser()->isAllowed('audio', 'stop')) {
            $this->error('Nemáte oprávnění k zastavení audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->stop_play();
    }
    
    public function actionDefault(){
        if (!$this->getUser()->isAllowed('audio', 'list')) {
            $this->error('Nemáte oprávnění k ovládání audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
}