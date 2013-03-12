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

require_once ('DataAccessController.php');

/**
 * Controller providing CRUD API for the field types JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_ContentTypesController extends Backoffice_DataAccessController
{
    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array('index', 'find-one', 'read-child', 'tree', 'model', 'get-readable-content-types', 'is-used', 'is-changeable');

    public function init() {
        parent::init();

        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('ContentTypes');
    }

    public function getReadableContentTypesAction() {
        return $this->_returnJson($this->_dataService->getReadableContentTypes());
    }

    public function isUsedAction() {
        $id = $this->getRequest()->getParam('id');
		$wasFiltered = Rubedo\Collection\AbstractCollection::disableUserFilter();
        $result = Rubedo\Services\Manager::getService('Contents')->isTypeUsed($id);
        Rubedo\Collection\AbstractCollection::disableUserFilter($wasFiltered);
        $this->_returnJson($result);
    }

    public function isChangeableAction() {
        $data = $this->getRequest()->getParams();
        $modifiedType = Zend_Json::decode($data['fields']);
        $id = $data['id'];
        $originalType = $this->_dataService->findById($id);
        $originalType = $originalType['fields'];

        $listResult = Rubedo\Services\Manager::getService('Contents')->getListByTypeId($id);
        if (is_array($listResult) && $listResult['count'] == 0) {
            $resultArray = array("modify" => "ok");
        } else {

            if (count($originalType) > count($modifiedType)) {
                $greaterData = $originalType;
                $tinierData = $modifiedType;
            } elseif (count($originalType) < count($modifiedType)) {
                $greaterData = $modifiedType;
                $tinierData = $originalType;
            } else {
                $greaterData = $originalType;
                $tinierData = $modifiedType;
            }

            $unauthorized = 0;

            $authorizedModif = array("text" => array('506441f8c648043912000017', '506441f8c648043912000018', '506441f8c648043912000019'), "number" => array('506441f8c64804391200001d', '506441f8c64804391200001e', '506441f8c64804391200001f'));
            foreach ($greaterData as $fieldToCompare) {
                foreach ($tinierData as $field) {
                    if ($fieldToCompare['config']['name'] == $field['config']['name']) {
                        if ($fieldToCompare['protoId'] != $field['protoId']) {
                            if (in_array($fieldToCompare['protoId'], $authorizedModif['text'])) {
                                if (!in_array($field['protoId'], $authorizedModif['text'])) {
                                    $unauthorized++;
                                }
                            } elseif (in_array($fieldToCompare['protoId'], $authorizedModif['number'])) {
                                if (!in_array($field['protoId'], $authorizedModif['number'])) {
                                    $unauthorized++;
                                }
                            } else {
                                $unauthorized++;
                            }
                        }
                    }
                }
            }
            $resultArray = ($unauthorized != 0) ? array("modify" => "no") : array("modify" => "possible");
        }
        $this->_returnJson($resultArray);
    }

}
