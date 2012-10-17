<?php

class LangController extends AbstractController
{

    public function chooseAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace('Default');
	    $askedLang = $this->_getParam('lg');
		if(in_array($askedLang, array("fr","en"))){
			$defaultNamespace->lang = $askedLang;
			$retour = array('success'=>$askedLang);
		}else{
			$retour = array('success'=>false);
		}
		$this->getResponse()->setBody(Zend_Json::encode($retour));
    }

	
}

