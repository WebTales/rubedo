<?php

class JavascriptController extends AbstractController
{
	
    public function getAction()
    {
    	$script = 'javascript/'.$this->_getParam('script').'.js';
		$twigVar = array();
        $this->twig($script,$twigVar);
    }
	
}

