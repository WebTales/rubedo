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
class Backoffice_DataAccessController extends AbstractController
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
     * temp data for tree view
     *
     * @var array
     */
	protected $_lostChildren = array();

    /**
     * Disable layout & rendering, set content type to json
     * init the store parameter if transmitted
     *
     * @see Zend_Controller_Action::init()
     */
    public function init()
    {
        parent::init();
        // refuse write action not send by POST
        if (!$this->getRequest()->isPost() && $this->getRequest()->getActionName() !== 'index') {
            //throw new \Exception('This action should be called by POST request');
        }

        // set the store value from the request is sent
        if (!isset($this->_store)) {
            $this->_store = $this->getRequest()->getParam('store');
        }

        if (!isset($this->_store)) {
            throw new Zend_Exception("No store parameter", 1);

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
    public function indexAction()
    {
        //$dataStore = $this->_dataReader->drop();

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

        $this->getResponse()->setBody(Zend_Json::encode($dataStore));
    }
	
	/**
     * The read as tree Action
     *
     * Return the content of the collection, get filters from the request
     * params
     *
     * @todo remove the temp hack when database starter is ready
     */
    public function treeAction()
    {
        //$dataStore = $this->_dataReader->drop();

        $dataStore = $this->_dataReader->read();
		
		$this->_lostChildren = array();

		foreach ($dataStore as $record) {
			$id = $record['id'];
			if(isset($record['parentId'])){
				$parentId = $record['parentId'];
				$this->_lostChildren[$parentId][$id] = $record; 
			}else{
				$rootId = $id;
				$rootRecord = $record;
			}
		}

		if(isset($rootRecord)){
			$result = array($this->_appendChild($rootRecord));
		}else{
			$result = array();
		}
		

        $this->getResponse()->setBody(Zend_Json::encode($result));
    }
	
	/**
	 * recursive function to rebuild tree from flat data store
	 * @param array $record root record of the tree
	 * @return array complete tree array
	 */
	protected function _appendChild(array $record){
		$id = $record['id'];
		if(isset($this->_lostChildren[$id])){
			$children = $this->_lostChildren[$id];
			foreach($children as $child){
				$record['children'][] = $this->_appendChild($child);
			}
		}
		return $record;
	}

    /**
     * The destroy action of the CRUD API
     */
    public function deleteAction()
    {
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $data = Zend_Json::decode($data);
            if (is_array($data)) {

                $returnArray = $this->_dataReader->destroy($data, true);

            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }

        } else {
            $returnArray = array('success' => false, "msg" => 'Invalid Data');
        }
        $this->getResponse()->setBody(json_encode($returnArray));
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction()
    {
        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $returnArray = $this->_dataReader->create($insertData, true);

            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }
        } else {
            $returnArray = array('success' => false, "msg" => 'No Data');
        }
        $this->getResponse()->setBody(json_encode($returnArray));
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction()
    {

        $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $updateData = Zend_Json::decode($data);
            if (is_array($updateData)) {

                $returnArray = $this->_dataReader->update($updateData, true);

            } else {
                $returnArray = array('success' => false, "msg" => 'Not an array');
            }
        } else {
            $returnArray = array('success' => false, "msg" => 'No Data');
        }
        $this->getResponse()->setBody(json_encode($returnArray));
    }

}
