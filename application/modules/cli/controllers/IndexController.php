<?php

/**
 * CLI controller
 * Invoked when using php in command line mode
 * Return response and Exit
 * @author jbourdin
 *
 */
class Cli_IndexController extends Zend_Controller_Action
{

    /** 
     * Disable layout and rendering
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
       parent::init();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getHelper('Layout')->disableLayout();
    }
    
    
    /**
     * Handle switching through arguments used in command line invocation
     */
    public function indexAction ()
    {
        $options = new Zend_Console_Getopt(
                array(
                        'i|init-db' => 'Initialize Database'
                ));
        
        if ($options->h) {
            echo $options->getUsageMessage();
            exit(0);
        }
        
        echo $options->getUsageMessage();
        exit(1);
    }
}

