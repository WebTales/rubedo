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
use Rubedo\Controller\Action;

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
class Backoffice_PagesController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'clear-orphan-pages',
        'count-orphan-pages',
        'model',
        'get-content-list',
        'holds-site-default',
        'add-localization'
    );

    public function init ()
    {
        parent::init();
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Pages');
    }

    /**
     * Clear orphan terms in the collection
     *
     * @return array Result of the request
     */
    public function clearOrphanPagesAction ()
    {
        $result = $this->_dataService->clearOrphanPages();
        
        $this->_returnJson($result);
    }

    public function countOrphanPagesAction ()
    {
        $result = $this->_dataService->countOrphanPages();
        
        $this->_returnJson($result);
    }

    /**
     * Check if page is or is the father of the default page of its site
     */
    public function holdsSiteDefaultAction ()
    {
        $id = $this->getRequest()->getParam('id');
        $result = array();
        $result['holdsDefault'] = $this->_dataService->hasDefaultPageAsChild($id);
        $result['success'] = true;
        $this->_returnJson($result);
    }

    public function getContentListAction ()
    {
        $returnArray = array();
        $total = 0;
        $contentArray = array();
        $data = $this->getRequest()->getParams();
        $params["pagination"] = array(
            "page" => $data['page'],
            "start" => $data["start"],
            "limit" => $data["limit"]
        );
        $page = $this->_dataService->findById($data['id']);
        $params['current-page'] = $data['id'];
        $mask = Rubedo\Services\Manager::getService('Masks')->findById($page['maskId']);
        
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
                    $response = Action::getInstance()->action('get-contents', $controller, 'blocks', $params);
                    $contentArray[] = $response->getBody();
                }
            }
            if (isset($contentArray) && ! empty($contentArray)) {
                foreach ($contentArray as $key => $content) {
                    $content = Zend_Json::decode($content);
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
        $this->_returnJson($returnArray);
    }
    
    public function addLocalizationAction(){
        \Rubedo\Collection\Pages::addLocalizationForCollection();
        $this->_helper->json(array('success'=>true));
    }
}