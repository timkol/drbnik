<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace App\Forms\Rendering;

use Nette,
    Nette\Utils\Html;
use Nette\Forms\Rendering;
use Nette\Forms\Form,
    Nette\Forms\Controls;

/**
 * Converts a Form into the HTML output.
 *
 * @author     David Grudl
 */
class DrbnikFormRenderer extends Rendering\DefaultFormRenderer {

    /**
     *  /--- form.container
     *
     *    /--- error.container
     *      .... error.item [.class]
     *    \---
     *
     *    /--- hidden.container
     *      .... HIDDEN CONTROLS
     *    \---
     *
     *    /--- group.container
     *      .... group.label
     *      .... group.description
     *
     *      /--- controls.container
     *
     *        /--- pair.container [.required .optional .odd]
     *
     *          /--- label.container
     *            .... LABEL
     *            .... label.suffix
     *            .... label.requiredsuffix
     *          \---
     *
     *          /--- control.container [.odd]
     *            .... CONTROL [.required .text .password .file .submit .button]
     *            .... control.requiredsuffix
     *            .... control.description
     *            .... control.errorcontainer + control.erroritem
     *          \---
     *        \---
     *      \---
     *    \---
     *  \--
     *
     * @var array of HTML tags */
    public $wrappers = array(
        'form' => array(
            'container' => NULL,
        ),
        'error' => array(
            'container' => 'ul class=error',
            'item' => 'li',
        ),
        'group' => array(
            'container' => 'fieldset',
            'label' => 'legend',
            'description' => 'p',
        ),
        'controls' => array(
            'container' => 'div class=form',
        ),
        'pair' => array(
            //'container' => 'div class=form_pair',
            'container' => 'div class="form_pair form-group"',
            '.required' => 'required',
            '.optional' => NULL,
            '.odd' => NULL,
            '.error' => 'has-error',
            //'.error' => NULL,
        ),
        'control' => array(
            //'container' => 'div class=form_control',
            'container' => 'div class="form_control col-sm-9"',
            '.odd' => NULL,
            //'description' => 'small',
            'description' => 'span class=help-block',
            'requiredsuffix' => '',
            //'errorcontainer' => 'span class=error',
            'errorcontainer' => 'span class=help-block',
            'erroritem' => '',
            '.required' => 'required',
            '.text' => 'text',
            '.password' => 'text',
            '.file' => 'text',
            '.submit' => 'button',
            '.image' => 'imagebutton',
            '.button' => 'button',
        ),
        'label' => array(
            //'container' => 'div class=form_label',
            'container' => 'div class="col-sm-3 control-label form_label"',
            'suffix' => NULL,
            'requiredsuffix' => '',
        ),
        'hidden' => array(
            'container' => 'div',
        ),
    );
    
    /**
	 * Provides complete form rendering.
	 * @param  Nette\Forms\Form
	 * @param  string 'begin', 'errors', 'ownerrors', 'body', 'end' or empty to render all
	 * @return string
	 */
	public function render(Nette\Forms\Form $form, $mode = NULL)
	{
            // make form and controls compatible with Twitter Bootstrap
            $form->getElementPrototype()->class('form-horizontal');
            foreach ($form->getControls() as $control) {
                if ($control instanceof Controls\Button) {
                    $control->getControlPrototype()->addClass(empty($usedPrimary) ? 'btn btn-primary' : 'btn btn-default');
                    //$control->getControlPrototype()->addClass('btn');
                    $usedPrimary = TRUE;
                } 
                else if ($control instanceof Controls\TextBase || $control instanceof Controls\SelectBox || $control instanceof Controls\MultiSelectBox) {
                    $control->getControlPrototype()->addClass('form-control');
                } 
                else if ($control instanceof Controls\Checkbox || $control instanceof Controls\CheckboxList || $control instanceof Controls\RadioList) {
                    $control->getSeparatorPrototype()->setName('div')->addClass($control->getControlPrototype()->type);
                }
            }
            
            //fix required bug
//            foreach ($form->getControls() as $control) {
//                if($control->isRequired()) {
//                    $control->getControlPrototype()->ahoj = 'required';
//                }
//            }
            
            return parent::render($form, $mode);
        }

}
