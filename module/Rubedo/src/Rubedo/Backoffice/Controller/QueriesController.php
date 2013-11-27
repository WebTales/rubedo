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

use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;

/**
 * Controller providing CRUD API for the Queries JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class QueriesController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();
        
        // init the data access service
        $this->_dataService = Manager::getService('Queries');
    }

    public function simulateResultAction()
    {
        $contentsService = Manager::getService('Contents');
        $data = $this->params()->fromQuery();
        if (isset($data['query'])) {

            $filters = $this->_dataService->getFilterArrayById($data['query']);
            if ($filters !== false) {
                $contentList = $contentsService->getOnlineList($filters['filter'], $filters["sort"], (($data['page']-1) * $data['limit']), intval($data['limit']));
            } else {
                $contentList = array(
                    'count' => 0
                );
            }
            if ($contentList["count"] > 0) {
                $returnArray=array();
                $returnArray["data"]=array();
                foreach ($contentList['data'] as $content) {
                    $returnArray["data"][] = array(
                        'text' => $content['text'],
                        'id' => $content['id']
                    );
                }
                $returnArray['total'] = $contentList["count"];
                $returnArray["success"] = true;
            } else {
                $returnArray = array(
                    "success" => false,
                    "msg" => "No contents found",
                    "data" => array()
                );
            }
        } else {
            $returnArray = array(
                "success" => false,
                "msg" => "No query found",
                "data" => array()
            );
        }

        return new JsonModel($returnArray);
    }
}