<?php

/**
 * Plugin to handle preMVC context
 * 
 * @author jbourdin
 *
 */
class Application_Plugin_Main extends Zend_Controller_Plugin_Abstract
{
    
    /*
     * (non-PHPdoc) @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
     */
    public function dispatchLoopStartup (
            Zend_Controller_Request_Abstract $request)
    {
        
        //switch to cli module if called by command line
        if (strtolower(php_sapi_name()) == 'cli') {
            $request->setModuleName('cli');
            $request->setControllerKey('index');
            $request->setActionName('index');
        }
        
    }
}
