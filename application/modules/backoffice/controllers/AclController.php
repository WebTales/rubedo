<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Controller providing access control list
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_AclController extends Zend_Controller_Action
{
    /**
     * should json be prettified
     *
     * @var bool
     */
    protected $_prettyJson = true;

    /**
     * Set the response body with Json content
     * Option : json is made human readable
     * @param mixed $data data to be json encoded
     */
    protected function _returnJson($data) {
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);

        $returnValue = Zend_Json::encode($data);
        if ($this->_prettyJson) {
            $returnValue = Zend_Json::prettyPrint($returnValue);
        }
        $this->getResponse()->setBody($returnValue);
    }

    function indexAction() {
        $AclArray = array();
        $dataJson = $this->getRequest()->getParam('data');
        if (isset($dataJson)) {
            $dataArray = Zend_Json::decode($dataJson);
            if (is_array($dataArray)) {
            	$aclService = \Rubedo\Services\Manager::getService('Acl');
				$AclArray = $aclService->accessList(array_keys($dataArray));
            }
        }

        return $this->_returnJson($AclArray);
    }

}
