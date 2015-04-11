<?php

namespace App\Forms;

use Nette,
	Nette\Application\UI\Form;


abstract class BaseFormFactory extends Nette\Object
{
    protected function create() {
        $form = new Form;
        $form->setRenderer(new \App\Forms\Rendering\DrbnikFormRenderer());
        return $form;
    }
}