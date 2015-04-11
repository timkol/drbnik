<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form,
	Nette\Security\User;


abstract class BaseFormFactory extends Nette\Object
{
    public function create() {
        $form = new Form;
        $form->setRenderer(new \App\Forms\Rendering\DrbnikFormRenderer());
        return $form;
    }
}