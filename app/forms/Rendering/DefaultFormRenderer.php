<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Forms\Rendering;

use Nette,
    Nette\Utils\Html;

/**
 * Converts a Form into the HTML output.
 *
 * @author     David Grudl
 */
class DrbnikDefaultFormRenderer extends DefaultFormRenderer {

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
            'container' => 'div class=form_pair',
            '.required' => 'required',
            '.optional' => NULL,
            '.odd' => NULL,
            '.error' => NULL,
        ),
        'control' => array(
            'container' => 'div class=form_control',
            '.odd' => NULL,
            'description' => 'small',
            'requiredsuffix' => '',
            'errorcontainer' => 'span class=error',
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
            'container' => 'div class=form_label',
            'suffix' => NULL,
            'requiredsuffix' => '',
        ),
        'hidden' => array(
            'container' => 'div',
        ),
    );

}
