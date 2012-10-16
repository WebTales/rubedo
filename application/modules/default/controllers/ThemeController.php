<?php

class ThemeController extends AbstractController
{

    public function chooseAction()
    {
    	$defaultNamespace = new Zend_Session_Namespace('Default');
	    $askedCss = $this->_getParam('css');
		if(in_array($askedCss, array("default","amelia","cerulean","cyborg","journal","readable","simplex","slate","spacelab","spruce","superhero","united"))){
			$defaultNamespace->themeCSS = $askedCss;
			$retour = array('success'=>$askedCss);
		}else{
			$retour = array('success'=>false);
		}
		$this->getResponse()->setBody(Zend_Json::encode($retour));
    }

	
}

