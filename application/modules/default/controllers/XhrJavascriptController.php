<?php

class XhrJavascriptController extends Zend_Controller_Action
{
	
    public function getScriptAction()
    {		
    	$script = 'javascript/'.$this->_getParam('script').'.js';
		$twigVar = array();	

		$content = Rubedo\Services\Manager::getService('FrontOfficeTemplates')->render($script, $twigVar);


		$this->getHelper('ViewRenderer')->setNoRender();
		$this->getHelper('Layout')->disableLayout();
		
        $this->getResponse()->appendBody($content, 'default');
    }
	
}

