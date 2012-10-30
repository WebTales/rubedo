<?php

class XhrJavascriptController extends Zend_Controller_Action
{
	
    public function getScriptAction()
    {
    	$this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
		$session = Rubedo\Services\Manager::getService('Session');
        $lang = $session->get('lang','fr');
		$this->_serviceTemplate->init($lang);
		
    	$script = 'javascript/'.$this->_getParam('script').'.js';
		$twigVar = array();
		

		$content = $this->_serviceTemplate->render($script, $twigVar);


		$this->getHelper('ViewRenderer')->setNoRender();
		$this->getHelper('Layout')->disableLayout();
		
        $this->getResponse()->appendBody($content, 'default');
    }
	
}

