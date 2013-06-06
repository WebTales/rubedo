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

/**
 * Controller providing Elastic Search querying
 *
 *
 *
 * @author dfanchon
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_ElasticSearchController extends Zend_Controller_Action
{

    protected $_option = 'all';

    public function indexAction ()
    {
        
        // get params
        $params = $this->getRequest()->getParams();
        
        // get option : all, dam, content, geo
        if (isset($params['option'])) {
            $this->_option = $params['option'];
        }
        
        // search over every sites
        $params['site'] = null;
        
        $query = \Rubedo\Services\Manager::getService('ElasticDataSearch');
        
        $query->init();
        if (isset($params['limit'])) {
            $params['pagesize'] = (int) $params['limit'];
        }
        if (isset($params['page'])) {
            $params['pager'] = (int) $params['page'] - 1;
        }
        if (isset($params['sort'])) {
            $sort = Zend_Json::decode($params['sort']);
            $params['orderby'] = ($sort[0]['property'] == 'score') ? '_score' : $sort[0]['property'];
            $params['orderbyDirection'] = $sort[0]['direction'];
        }
        
        $results = $query->search($params, $this->_option);
        
        $results['success'] = true;
        $results['message'] = 'OK';
        
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        
        $returnValue = Zend_Json::encode($results);
        $returnValue = Zend_Json::prettyPrint($returnValue);
        
        $this->getResponse()->setBody($returnValue);
    }

    public function getOptionsAction ()
    {
        $esOptions = Rubedo\Elastic\DataAbstract::getOptions();
        $returnArray = array();
        $returnArray['success'] = true;
        $returnArray['data'] = $esOptions;
        $this->_helper->json($returnArray);
    }
}
