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

class SpellPresenter extends BasePresenter
{    
    public function renderList() {
        if (!$this->getUser()->isAllowed('spell', 'list')) {
            $this->error('Nemáte oprávnění k sesílání kouzel.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        $this->template->spells = [
            'lumos' => 'Lumos',
            'nox' => 'Nox',
            'avada kedavra' => 'Avada kedavra'
        ];
    }
    
    public function actionCast($spell) {
        if (!$this->getUser()->isAllowed('spell', 'cast')) {
            $this->error('Nemáte oprávnění k sesílání kouzel.', \Nette\Http\IResponse::S403_FORBIDDEN);
        }
        file_get_contents("http://localhost:3000/spell?name=".$spell);
        $this->flashMessage('Seslání proběhlo úspěšně', 'success');
        $this->redirect("list");
    }
}