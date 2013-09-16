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

use Rubedo\Controller\Action;
use Rubedo\Services\Manager;
use Zend\Json\Json;

/**
 * Controller providing CRUD API for the Pages JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class PagesController extends DataAccessController
{
    public function __construct()
    {
        parent::__construct();
        // init the data access service
        $this->_dataService = Manager::getService('Pages');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanPagesAction()
    {
        $result = $this->_dataService->clearOrphanPages();
        
        return $this->_returnJson($result);
    }

    public function countOrphanPagesAction()
    {
        $result = $this->_dataService->countOrphanPages();
        
        return $this->_returnJson($result);
    }

    /**
     * Check if page is or is the father of the default page of its site
     */
    public function holdsSiteDefaultAction()
    {
        $id = $this->params()->fromQuery('id');
        $result = array();
        $result['holdsDefault'] = $this->_dataService->hasDefaultPageAsChild($id);
        $result['success'] = true;
        return $this->_returnJson($result);
    }

    
    /**
     * @todo comment what this action do
     * @todo migrate to ZF2
     */
    public function getContentListAction()
    {
        $returnArray = array();
        $total = 0;
        $contentArray = array();
        $data = $this->params()
            ->fromQuery();
        $params["pagination"] = array(
            "page" => $data['page'],
            "start" => $data["start"],
            "limit" => $data["limit"]
        );
        $page = $this->_dataService->findById($data['id']);
        $params['current-page'] = $data['id'];
        $mask = Manager::getService('Masks')->findById($page['maskId']);
        
        $pageBlocks = array_merge($page['blocks'], $mask['blocks']);
        
        if ($pageBlocks != array()) {
            foreach ($pageBlocks as $block) {
                switch ($block['bType']) {
                    case 'Carrousel':
                    case 'carrousel':
                        $controller = 'carrousel';
                        break;
                    case 'Liste de Contenus':
                    case 'contentList':
                        $controller = 'content-list';
                        break;
                    case 'Détail de contenu':
                    case 'contentDetail':
                        $controller = 'content-single';
                        break;
                    default:
                        $controller = false;
                }
                if ($controller != false) {
                    $params["block"] = $block['configBloc'];
                    $controller = Manager::getService('Blocks')->getController($block['bType']);
                    // Clone global request and override it woth block params
                    $queryString = $this->getRequest()->getQuery();
                    $blockQueryString = clone ($queryString);
                    
                    foreach ($params as $key => $value) {
                        $blockQueryString->set($key, $value);
                    }
                    $this->getRequest()->setQuery($blockQueryString);
                    
                    // run block and get response
                    $result = $this->forward()->dispatch($controller, array(
                        'action' => 'get-contents'
                    ));
                    
                    // set back global query
                    $this->getRequest()->setQuery($queryString);
                    $contentArray[] = $result->getVariables();
                }
            }
            if (isset($contentArray) && ! empty($contentArray)) {
                foreach ($contentArray as $key => $content) {
                    if ($content["success"] == true) {
                        $total = $total + $content["total"];
                        unset($content['total']);
                        unset($content['success']);
                        foreach ($content as $vignette) {
                            $returnArray["data"][] = $vignette;
                        }
                    } else {
                        unset($contentArray[$key]);
                    }
                }
                if ($total != 0) {
                    $returnArray["total"] = $total;
                } else {
                    $returnArray = array(
                        "success" => true,
                        "msg" => "No contents found",
                        "data" => array()
                    );
                }
            } else {
                $returnArray = array(
                    "success" => true,
                    "msg" => "No contents found",
                    "data" => array()
                );
            }
        } else {
            $returnArray = array(
                "success" => true,
                "msg" => "No blocks found on this page",
                "data" => array()
            );
        }
        return $this->_returnJson($returnArray);
    }
}