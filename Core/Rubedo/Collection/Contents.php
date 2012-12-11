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
namespace Rubedo\Collection;
use Rubedo\Interfaces\Collection\IContents;
use Rubedo\Services\Manager;

/**
 * Service to handle contents
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Contents extends WorkflowAbstractCollection implements IContents
{

    /**
     * Is the input obj is valid
     *
     * @var bool
     */
    protected $_isValidInput = false;

    /**
     * contains found errors when validating input data
     *
     * @var array
     */
    protected $_inputDataErrors = array();

    public function __construct ()
    {
        $this->_collectionName = 'Contents';
        parent::__construct();
    }

    /**
     * ensure that no nested contents are requested directly
     */
    protected function _init ()
    {
        parent::_init();
        $this->_dataService->addToExcludeFieldList(
                array(
                        'nestedContents'
                ));
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::create()
     */
    public function create (array $obj, $safe = true, $live = true)
    {
        $obj = $this->_filterInputData($obj);
        if ($this->_isValidInput) {
            $returnArray = parent::create($obj, $safe, $live);
        } else {
            $returnArray = array(
                    'success' => false,
                    'msg' => 'invalid input data',
                    'inputErrors' => $this->_inputDataErrors
            );
        }
        return $returnArray;
    }
    
    /*
     * (non-PHPdoc) @see \Rubedo\Collection\WorkflowAbstractCollection::update()
     */
    public function update (array $obj, $safe = true, $live = true)
    {
        $obj = $this->_filterInputData($obj);
        if ($this->_isValidInput) {
            $returnArray = parent::update($obj, $safe, $live);
        } else {
            $returnArray = array(
                    'success' => false,
                    'msg' => 'invalid input data',
                    'inputErrors' => $this->_inputDataErrors
            );
        }
        return $returnArray;
    }

    /**
     * Return validated data from input data based on content type
     *
     * @param array $obj            
     * @return array:
     */
    protected function _filterInputData (array $obj)
    {
        $contentTypeId = $obj['typeId'];
        $contentType = Manager::getService('ContentTypes')->findById(
                $contentTypeId);
        $contentTypeFields = $contentType['fields'];
        $fieldsArray = array();
        $missingField = array();
        foreach ($contentTypeFields as $value) {
            $fieldsArray[$value['config']['name']] = $value;
            if(!isset($value['config']['allowBlank']) || ! $value['config']['allowBlank']){
                $missingField[$value['config']['name']] = $value['config']['name'];
            }
        }
        
        $fieldsList = array_keys($fieldsArray);
        $tempFields = array();
        $tempFields['text'] = htmlspecialchars($obj['text']);
        
        foreach ($obj['fields'] as $key => $value) {
            if($key == 'text'){
                continue;
            }
            if (! in_array($key, $fieldsList)) {
                $this->_inputDataErrors[$key] = 'unknown field';
            } else {
                unset($missingField[$key]);
                
                switch ($fieldsArray[$key]['cType']) {
                    case 'CKEField':
                        $tempFields[$key] = Manager::getService('HtmlCleaner')->clean($value);
                        break;
                    default:
                        $tempFields[$key] = htmlspecialchars($value);
                        break;
                }
            }
        }
        
        $obj['fields'] = $tempFields;
        
        if (count($missingField) > 0) {
            foreach ($missingField as $value){
                $this->_inputDataErrors[$key] = 'missing field';
            }
        }
        
        
        if (count($this->_inputDataErrors) === 0) {
            $this->_isValidInput = true;
        }
        
        return $obj;
    }
}
