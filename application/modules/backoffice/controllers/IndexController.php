<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */

/**
 * Back Office Defautl Controller
 * 
 * Invoked when calling /backoffice URL
 *
 * @author jbourdin
 *
 */
class Backoffice_IndexController extends Zend_Controller_Action
{



    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->getHelper('Layout')
            ->disableLayout();
        $this->getHelper('ViewRenderer')
            ->setNoRender();
        $this->getResponse()
            ->setBody(file_get_contents(APPLICATION_PATH . '/rubedo-backoffice-ui/www/app.html'));
    }

    /**
     * Return the Ext/Js main JS
     */
    public function appjsAction ()
    {
        $this->getHelper('Layout')
            ->disableLayout();
        $this->getHelper('ViewRenderer')
            ->setNoRender();
        $this->getResponse()
            ->setHeader('Content-Type', "application/javascript");
        
        $this->getResponse()
            ->setBody(file_get_contents(APPLICATION_PATH . '/rubedo-backoffice-ui/www/app.js'));
    }
}

