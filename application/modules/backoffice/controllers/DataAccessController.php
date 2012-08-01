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
 * @version $Id:
 */

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Controller providing CRUD API and dealing with the data access
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_DataAccessController extends Zend_Controller_Action
{

    /**
     * Name of the store which is also to the collection name
     *
     * @var string
     */
    protected $_store;

    /**
     * Data Access Service
     *
     * @var DataAccess
     */
    protected $_dataReader;

    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        // refuse write action not send by POST
        if (! $this->getRequest()->isPost() && $this->getRequest()->getActionName() !== 'index') {
            throw new \Exception('This action should be called by POST request');
        }
        
        // set the store value from the request is sent
        if (! isset($this->_store)) {
            $this->_store = $this->getRequest()->getParam('store');
        }
        
        // disable layout and set content type
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type', "application/json", true);
        
        // init the data access service
        $this->_dataReader = Rubedo\Services\Manager::getService('MongoDataAccess');
        $this->_dataReader->init($this->_store);
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params
     * 
     * @todo remove the temp hack when database starter is ready
     */
    public function indexAction ()
    {
        
        
        
        
        $request = $this->getRequest();
        
        $page = $request->getParam('page', 1);
        $start = $request->getParam('start', 0);
        $limit = $request->getParam('limit', 25);
        $group = $request->getParam('group', false);
        if ($group) {
            $group = Zend_Json::decode($group);
        }
        $sort = $request->getParam('sort', false);
        if ($sort) {
            $sort = Zend_Json::decode($sort);
        }
        
        
        $dataStore = $this->_dataReader->read();
        
        // temp hack to use the json files of the UI prototype
        if (empty($dataStore)) {
            
            $oldStore = file_get_contents(APPLICATION_PATH . '/rubedo-backoffice-ui/www/data/' . $this->_store . '.json');
            $dataStore = Zend_Json::decode($oldStore);
            foreach ($dataStore as $data) {
                $this->_dataReader->create($data, true);
            }
            $dataStore = $this->_dataReader->read();
        }
        
        // temp hack to test these option on a single store
        if ($this->_store == 'PersonalPrefs') {
            $reponse = array();
            $reponse['data'] = array_values($dataStore);
            $reponse['total'] = count($reponse['data']);
            $reponse['success'] = TRUE;
            $reponse['message'] = 'OK';
            $this->getResponse()->setBody(json_encode($reponse));
            return;
        }
        
        // return the data in a JSON content
        $this->getResponse()->setBody(json_encode(array_values($dataStore)));
    }

    /**
     * The destroy action of the CRUD API
     */
    public function destroyAction ()
    {
        $request = $this->getRequest();
        $returnArray = array();
        
        Zend_Debug::dump($this->getRequest()->getParams());
        Zend_Debug::dump($this->getRequest()->getPost());
        Zend_Debug::dump($this->getRequest()->getRawBody());
        
        // $this -> getResponse() -> setBody(json_encode($returnArray));
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction ()
    {
        $request = $this->getRequest();
        $returnArray = array();
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction ()
    {
        $request = $this->getRequest();
        $returnArray = array();
    }
}
