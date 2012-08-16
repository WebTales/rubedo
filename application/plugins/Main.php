<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

/**
 * Plugin to handle preMVC context
 * 
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Application_Plugin_Main extends Zend_Controller_Plugin_Abstract
{
    
    /**
     * Called before Zend_Controller_Front enters its dispatch loop.
     *
     * @param  Zend_Controller_Request_Abstract $request
     * @return void
     * @see Zend_Controller_Plugin_Abstract::dispatchLoopStartup()
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
        
        //ajoute en variable de classe métier l'objet gestionnaire de cache à durée de vie illimitée
//         if(ENABLE_CACHE){
//             Application_Model_Services_Injecteur_Cache::setCache(Zend_Controller_Front::getInstance()->getParam('bootstrap')
//             ->getResource('cachemanager')
//             ->getCache('unlimited'));
//         }
        
        /*
         * Processus d'authentification : on est admin pour le mode CLI, on fait appel au certificat sinon
        */
//         $authentificationService = Application_Model_Services_Manager::getService('Authentification');
//         if(self::$_module === 'Cli'){
//             $authentificationService->setRole('admin');
//         }
        
//         define('USER_ROLE',$authentificationService->getRole());
        
    }
}
