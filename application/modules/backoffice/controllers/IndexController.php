<?php

class Backoffice_IndexController extends Zend_Controller_Action
{

    public function init ()
    {
        /* Initialize action controller here */
    }

    public function indexAction ()
    {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setBody(
                file_get_contents(
                        APPLICATION_PATH . '/rubedo-backoffice-ui/www/app.html'));
    }

    public function appjsAction ()
    {
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', 
                "application/javascript");
        
        $this->getResponse()->setBody(
                file_get_contents(
                        APPLICATION_PATH . '/rubedo-backoffice-ui/www/app.js'));
    }
}

