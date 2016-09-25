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
            $this->template->files = $this->model->listAudio();
    }
    
    public function renderCrontab() {
            $this->template->crontab = $this->model->readFutureCronTab();
    }

    protected function createComponentAddForm() {
        $form = $this->factory->createAddFileForm();
	$form->onSuccess[] = function ($form) {        
            $form->getPresenter()->flashMessage('Přidání proběhlo úspěšně.', 'success');
            $form->getPresenter()->redirect('Audio:default');
	};
	return $form;
    }
    
    protected function createComponentAddCronTabForm() {
        $form = $this->factory->createAddCronTabForm();
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
        $this->model->deleteAudio($filename);
    }
    
    public function actionPlay($filename, $repetitions = 1){
        if (!$this->getUser()->isAllowed('audio', 'play')) {
            $this->error('Nemáte oprávnění k přehrátí audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->playAudio($filename, $repetitions);
    }
    
    public function actionStop(){
        if (!$this->getUser()->isAllowed('audio', 'stop')) {
            $this->error('Nemáte oprávnění k zastavení audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->stopPlayAudio();
    }
    
    public function actionDefault(){
        if (!$this->getUser()->isAllowed('audio', 'list')) {
            $this->error('Nemáte oprávnění k ovládání audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    public function actionCrontab(){
        if (!$this->getUser()->isAllowed('audio', 'listCron')) {
            $this->error('Nemáte oprávnění k ovládání automatizovaného audio výstupu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    public function actionPlayMic(){
        if (!$this->getUser()->isAllowed('audio', 'playMic')) {
            $this->error('Nemáte oprávnění k ovládání mikrofonu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->stopPlayAudio();
        $this->model->playMic();
    }
    
    public function actionStopMic(){
        if (!$this->getUser()->isAllowed('audio', 'stopMic')) {
            $this->error('Nemáte oprávnění k ovládání mikrofonu.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->model->stopMic();
    }
}