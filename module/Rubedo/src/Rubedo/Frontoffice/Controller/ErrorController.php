<?php

/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Frontoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Exceptions\Access;

/**
 * Dead end error controller
 *
 * Invoked when something failed on bootstrap.
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class ErrorController extends AbstractActionController
{

    /**
     * Main Action of this controller
     *
     * Throw Error from context
     */
    public function indexAction()
    {
        $exception = $this->params()->fromQuery('exception', new Access('Deadend controller'));
        throw $exception;
    }
}

