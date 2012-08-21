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
 * Abstract controller
 *
 * Implements access control check
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractController extends Zend_Controller_Action
{

    public function init()
    {
        $serviceAcl = \Rubedo\Services\Manager::getService('Acl');
		$hasAccess = $serviceAcl->hasAccess('controller.'.$this->getRequest()->getControllerName().'.'.$this->getRequest()->getActionName());
		
		if(!$hasAccess){
			throw new \Zend_Acl_Exception("Access Refused To current Controller", 1);
			
		}

    }

}
