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
namespace Rubedo\Backoffice\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Rubedo\Collection\AbstractLocalizableCollection;
use Zend\Json\Json;
use Rubedo\Elastic\DataAbstract;
use Zend\View\Model\JsonModel;


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
class ElasticSearchController extends AbstractActionController
{

    protected $_option = 'all';

    public function __construct()
    {        
        // initialize
        // localized
        // collections
        $serviceLanguages = Manager::getService('Languages');
        if ($serviceLanguages->isActivated()) {
            $workingLanguage = $this->params()->fromQuery('workingLanguage');
            if ($workingLanguage && $serviceLanguages->isActive($workingLanguage)) {
                AbstractLocalizableCollection::setWorkingLocale($workingLanguage);
            } else {
                AbstractLocalizableCollection::setWorkingLocale($serviceLanguages->getDefaultLanguage());
            }
        }
    }

    public function indexAction ()
    {
        
        // get params
        $params = $this->params()->fromQuery();
        
        // get option : all, dam, content, geo
        if (isset($params['option'])) {
            $this->_option = $params['option'];
        }
        
        // search over every sites
        $params['site'] = null;
        
        $query = Manager::getService('ElasticDataSearch');
        
        $query->init();
        if (isset($params['limit'])) {
            $params['pagesize'] = (int) $params['limit'];
        }
        if (isset($params['page'])) {
            $params['pager'] = (int) $params['page'] - 1;
        }
        if (isset($params['sort'])) {
            $sort = Json::decode($params['sort'],Json::TYPE_ARRAY);
            $params['orderby'] = ($sort[0]['property'] == 'score') ? '_score' : $sort[0]['property'];
            $params['orderbyDirection'] = $sort[0]['direction'];
        }
        
        $results = $query->search($params, $this->_option);
        
        $results['success'] = true;
        $results['message'] = 'OK';
        
        return new JsonModel($results);
    }

    public function getOptionsAction ()
    {
        $esOptions = DataAbstract::getOptions();
        $returnArray = array();
        $returnArray['success'] = true;
        $returnArray['data'] = $esOptions;
        return new JsonModel($returnArray);
    }
}
