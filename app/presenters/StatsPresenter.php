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

/**
 * Gossip presenter.
 */
class StatsPresenter extends BasePresenter
{
    
    /** @var Nette\Database\Context */
    private $database;
    
    /** @var GossipManager */
    private $model;
    
    public function __construct(Nette\Database\Context $database, GossipManager $model)
    {
        parent::__construct();
        $this->database = $database;
        $this->model = $model;
    }
    
    public function renderDefault() {
        $options = array(
            'new' => 'Nové',
            'approved' => 'Přijaté',
            'rejected' => 'Zamítnuté',
            'duplicit' => 'Duplicitní',
        );
        
        $this->template->gossips = array();
        foreach ($options as $status => $name) {
            $this->template->gossips[$name] = $this->model->getByStatus($status)->count();
        }
    }

    public function renderAuthors() {
        $options = array(
            'new' => 'Nové',
            'approved' => 'Přijaté',
            'rejected' => 'Zamítnuté',
            'duplicit' => 'Duplicitní',
        );
        $this->template->options = $options;
        
        $this->template->authors = array();
        $authors = $this->database->table('person');
        foreach ($authors as $author) {
            $authorName = $this->model->getPersonDisplayName($author);
            $this->template->authors[$authorName]['all'] = 0;
            foreach ($options as $status => $name) {
                $this->template->authors[$authorName][$name] = $this->model->getByAuthor($author->person_id, $status)->getRowCount();
                $this->template->authors[$authorName]['all'] += $this->template->authors[$authorName][$name];
            }
        }
        arsort($this->template->authors);
    }
    
    public function actionDefault(){
        if (!$this->getUser()->isAllowed('stats', 'show')) {
            $this->error('Nemáte oprávnění ke zobrazení statistik.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
    
    public function actionAuthors(){
        if (!$this->getUser()->isAllowed('stats', 'show')) {
            $this->error('Nemáte oprávnění ke zobrazení statistik.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
    }
}