<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
Use Rubedo\Services\Manager;
/**
 * Front End Edition controller
 *
 * @author nduvollet
 * @category Rubedo
 * @package Rubedo
 */
class XhrEditController extends Zend_Controller_Action
{

    /**
     * variable for the Session service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_session;

    /**
     * variable for the Data service
     *
     * @param
     *            Rubedo\Interfaces\User\ISession
     */
    protected $_dataService;

    /**
     * Init the session service
     */
    public function init ()
    {
        $this->_dataService = Manager::getService('Contents');
    }

    /**
     * Allow to define the current theme
     */
    public function indexAction ()
    {
    	$contentId = $this->getParam("id", null);
    	$data = $this->getParam("data", null);
    	
    	$contentId = explode("_", $contentId);
    	$id = $contentId[0];
    	$field = $contentId[1];
    	
    	$field=explode("-",$field);
    	$name=$field[0];
    	$index=$field[1];	
    	if($id === null || $data === null || $name === null){
    		throw new \Rubedo\Exceptions\Server("Vous devez fournir l'identifiant du contenu concerné, la nouvelle valeur et le champ à mettre à jour en base de donnée");
    	}
    	
    	$content = $this->_dataService->findById($id, true, false);
    	if(!$content) {
    		throw new \Rubedo\Exceptions\Server("L'identifiant de contenu n'éxiste pas");
    	}
    	if ($content["status"] !== 'published') {
    		$returnArray['success'] = false;
    		$returnArray['msg'] = 'Content already have a draft version';
    	}else{
	    	if(count($field)>1)
	    		$content['fields'][$name][$index] = $data;
	    	else
	    		$content['fields'][$name] = $data;
	    	
	    	$updateResult = $this->_dataService->update($content,array("safe"=>true),false);
	    	
	    	if($updateResult['success']){
	    		return $this->_helper->json(array("success" => true));
	    	} else {
	    		return $this->_helper->json(array("success" => false, "msg" => "An error occured during the update of the content"));
	    	}	
    	}
    }
   
}
