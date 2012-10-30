<?php

class JavascriptController extends Zend_Controller_Action
{
	
    public function getAction()
    {
    	$script = 'javascript/'.$this->_getParam('script').'.js';
		$twigVar = array();
        $this->twig($script,$twigVar);
    }
	
}

