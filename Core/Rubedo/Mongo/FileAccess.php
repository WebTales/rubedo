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
namespace Rubedo\Mongo;

use Rubedo\Interfaces\Mongo\IFileAccess;

/**
 * Class implementing the API to MongoDB
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FileAccess extends DataAccess implements IFileAccess
{

    /**
     * Object which represent the mongoDB Collection
     *
     * @var \MongoGridFS
     */
    protected $_collection;



    /**
     * Initialize a data service handler to read or write in a MongoDb
     * Collection
     *
     * @param string $collection name of the DB
     * @param string $dbName name of the DB
     * @param string $mongo connection string to the DB server
     */
    public function init($collection=null, $dbName = null, $mongo = null) {
    	unset($collection);
        $mongo = self::$_defaultMongo;
        $dbName = self::$_defaultDb;

        $this->_adapter = new \Mongo($mongo);
        $this->_dbName = $this->_adapter->$dbName;
        $this->_collection = $this->_dbName->getGridFS();

    }


    /**
     * Do a find request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::read()
     * @return array
     */
    public function read() {
        //get the UI parameters
        $filter = $this->getFilterArray();
        $sort = $this->getSortArray();
        $firstResult = $this->getFirstResult();
        $numberOfResults = $this->getNumberOfResults();
        $includedFields = $this->getFieldList();
        $excludedFields = $this->getExcludeFieldList();

        //merge the two fields array to obtain only one array with all the conditions
        if (!empty($includedFields) && !empty($excludedFields)) {
            $fieldRule = $includedFields;
        } else {
            $fieldRule = array_merge($includedFields, $excludedFields);
        }

        //get the cursor
        $cursor = $this->_collection->find($filter, $fieldRule);
        $nbItems = $cursor->count();

        //apply sort, paging, filter
        $cursor->sort($sort);
        $cursor->skip($firstResult);
        $cursor->limit($numberOfResults);
		
		$data = array();
        //switch from cursor to actual array
        foreach ($cursor as $key => $value) {
            $data[]=$value;
        }


        //return data as simple array with no keys
        $datas = array_values($data);
		$returnArray = array("data"=>$datas,'count'=>$nbItems);
        return $returnArray;
    }

    /**
     * Do a findone request on the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::findOne()
     * @param array $value search condition
     * @return array
     */
    public function findOne($value) {
        //get the UI parameters
        $includedFields = $this->getFieldList();
        $excludedFields = $this->getExcludeFieldList();

        //merge the two fields array to obtain only one array with all the conditions
        if (!empty($includedFields) && !empty($excludedFields)) {
            $fieldRule = $includedFields;
        } else {
            $fieldRule = array_merge($includedFields, $excludedFields);
        }

        $value = array_merge($value, $this->getFilterArray());

        $mongoFile = $this->_collection->findOne($value, $fieldRule);

        return $mongoFile;
    }


    /**
     * Create an objet in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::create
     * @param array $obj data object
     * @param bool $safe should we wait for a server response
     * @return array
     */
    public function create(array $obj, $safe = true) {

        $obj['version'] = 1;
		$filename = $obj['serverFilename'];
		unset($obj['serverFilename']);

        $currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
        $currentUser = $currentUserService->getCurrentUserSummary();
        $obj['lastUpdateUser'] = $currentUser;
        $obj['createUser'] = $currentUser;

        $currentTimeService = \Rubedo\Services\Manager::getService('CurrentTime');
        $currentTime = $currentTimeService->getCurrentTime();

        $obj['createTime'] = $currentTime;
        $obj['lastUpdateTime'] = $currentTime;


        $fileId = $this->_collection->put($filename,$obj);
		
        if ($fileId) {
            $obj['id'] = (string)$fileId;
            $returnArray = array('success' => true, "data" => $obj);
        } else {
            $returnArray = array('success' => false);
        }
        
        return $returnArray;
    }
	
	/**
     * Delete objets in the current collection
     *
     * @see \Rubedo\Interfaces\IDataAccess::destroy
     * @param array $obj data object
     * @return array
     */
    public function destroy(array $obj,$safe = true) {
        $id = $obj['id'];
        //if (!isset($obj['version'])) {
        //    throw new \Rubedo\Exceptions\DataAccess('can\'t destroy an object without a version number.');
        //}
        //$version = $obj['version'];
        $mongoID = $this->getId($id);

        $updateCondition = array('_id' => $mongoID);

        if (is_array($this->_filterArray)) {
            $updateCondition = array_merge($this->_filterArray, $updateCondition);
        }

        $resultArray = $this->_collection->remove($updateCondition, array("safe" => $safe));
        if ($resultArray['ok'] == 1) {
            if ($resultArray['n'] == 1) {
                $returnArray = array('success' => true);
            } else {
                $returnArray = array('success' => false, "msg" => 'no record had been deleted');
            }

        } else {
            $returnArray = array('success' => false, "msg" => $resultArray["err"]);
        }
        
        return $returnArray;
    }
	
	public function drop(){
		return $this->_collection->drop();
	}
}
