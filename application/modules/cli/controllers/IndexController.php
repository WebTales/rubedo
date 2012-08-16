<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

/**
 * CLI controller
 * Invoked when using php in command line mode
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Cli_IndexController extends Zend_Controller_Action
{

    /**
     * Disable layout and rendering
     *
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        parent::init();
        $this->getHelper('ViewRenderer')
            ->setNoRender();
        $this->getHelper('Layout')
            ->disableLayout();
    }

    /**
     * Handle switching through arguments used in command line invocation
     */
    public function indexAction ()
    {
        $options = new Zend_Console_Getopt(array('h|help' => 'Display available options for Rubedo CLI Mode'));
        
        if ($options->h) {
            echo $options->getUsageMessage();
        } else {
            echo $options->getUsageMessage();
        }
    }
}