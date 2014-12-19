<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Backoffice\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\Json\Json;
use Zend\View\Model\JsonModel;


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
class ContentsController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();

        // init the data access service
        $this->_dataService = Manager::getService('Contents');
    }

    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction()
    {
        // merge filter and tFilter
        $jsonFilter = $this->params()->fromQuery('filter', '[]');
        $jsonTFilter = $this->params()->fromQuery('tFilter', '[]');
        $filterArray = Json::decode($jsonFilter, Json::TYPE_ARRAY);
        $tFilterArray = Json::decode($jsonTFilter, Json::TYPE_ARRAY);

        $filters = array_merge($tFilterArray, $filterArray);
        $mongoFilters = $this->_buildFilter($filters);

        $sort = Json::decode($this->params()->fromQuery('sort', null), Json::TYPE_ARRAY);
        $start = Json::decode($this->params()->fromQuery('start', null), Json::TYPE_ARRAY);
        $limit = Json::decode($this->params()->fromQuery('limit', null), Json::TYPE_ARRAY);

        $dataValues = $this->_dataService->getList($mongoFilters, $sort, $start, $limit, false);
        $response = array();
        $response['total'] = $dataValues['count'];
        $response['data'] = $dataValues['data'];
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        return $this->_returnJson($response);
    }

    /**
     * read child action
     *
     * Return the children of a node
     */
    public function readChildAction()
    {
        $filterJson = $this->params()->fromQuery('filter');
        if (isset($filterJson)) {
            $filters = Json::decode($filterJson, Json::TYPE_ARRAY);
        } else {
            $filters = null;
        }
        $sortJson = $this->params()->fromQuery('sort');
        if (isset($sortJson)) {
            $sort = Json::decode($sortJson, Json::TYPE_ARRAY);
        } else {
            $sort = null;
        }

        $parentId = $this->params()->fromQuery('node', 'root');
        $mongoFilters = $this->_buildFilter($filters);
        $dataValues = $this->_dataService->readChild($parentId, $mongoFilters, $sort, false);

        $response = array();
        $response['children'] = array_values($dataValues);
        $response['total'] = count($response['children']);
        $response['success'] = TRUE;
        $response['message'] = 'OK';

        return $this->_returnJson($response);
    }

    /**
     * The create action of the CRUD API
     */
    public function createAction()
    {
        $data = $this->params()->fromPost('data');

        if (!is_null($data)) {
            $insertData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($insertData)) {
                $insertData["target"] = isset($insertData["target"]) ? $insertData["target"] : array();
                $returnArray = $this->_dataService->create($insertData, array(), false);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * The update action of the CRUD API
     */
    public function updateAction()
    {
        $data = $this->params()->fromPost('data');

        if (!is_null($data)) {
            $updateData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($updateData)) {

                $returnArray = $this->_dataService->update($updateData, array(), false);
            } else {
                $returnArray = array(
                    'success' => false,
                    "msg" => 'Not an array'
                );
            }
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }
        if (!$returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }
        return $this->_returnJson($returnArray);
    }

    /**
     * Do a findOneAction
     */
    public function findOneAction()
    {
        $contentId = $this->params()->fromQuery('id');

        if (!is_null($contentId)) {

            $return = $this->_dataService->findById($contentId, false, false);

            if (empty($return['id'])) {

                $returnArray = array(
                    'success' => false,
                    "msg" => 'Object not found'
                );
            } else {

                $returnArray = array(
                    'succes' => true,
                    'data' => $return
                );
            }
        } else {

            $returnArray = array(
                'success' => false,
                "msg" => 'Missing param'
            );
        }

        return $this->_returnJson($returnArray);
    }

    /**
     * Return a list of ordered objects
     */
    public function getOrderedListAction()
    {
        // merge filter and tFilter
        $jsonFilter = $this->params()->fromQuery('filter', '[]');
        $jsonTFilter = $this->params()->fromQuery('tFilter', '[]');
        $filterArray = Json::decode($jsonFilter, Json::TYPE_ARRAY);
        $tFilterArray = Json::decode($jsonTFilter, Json::TYPE_ARRAY);

        $filters = array_merge($tFilterArray, $filterArray);
        $sort = Json::decode($this->params()->fromQuery('sort', null), Json::TYPE_ARRAY);
        $start = Json::decode($this->params()->fromQuery('start', null), Json::TYPE_ARRAY);
        $limit = Json::decode($this->params()->fromQuery('limit', null), Json::TYPE_ARRAY);

        $mongoFilters = $this->_buildFilter($filters);
        return new JsonModel($this->_dataService->getOrderedList($mongoFilters, $sort, $start, $limit, false));
    }

    public function clearOrphanContentsAction()
    {
        $result = $this->_dataService->clearOrphanContents();

        return $this->_returnJson($result);
    }

    public function countOrphanContentsAction()
    {
        $result = $this->_dataService->countOrphanContents();

        return $this->_returnJson(array("orphanContents" => $result));
    }

    public function deleteByContentTypeIdAction()
    {
        $typeId = $this->params()->fromPost('type-id');
        if (!$typeId) {
            throw new \Rubedo\Exceptions\User('This action needs a type-id as argument.', 'Exception3');
        }
        $deleteResult = $this->_dataService->deleteByContentType($typeId);

        return $this->_returnJson($deleteResult);
    }

    public function getStockAction()
    {
        $typeId = $this->params()->fromQuery('type-id');
        $workingLanguage = $this->params()->fromQuery('workingLanguage', "en");
        if (!$typeId) {
            throw new \Rubedo\Exceptions\User('This action needs a type-id as argument.', 'Exception3');
        }
        $result = Manager::getService("Stock")->getStock($typeId, $workingLanguage);
        return $this->_returnJson($result);
    }

    public function updateStockAction()
    {
        $data = $this->params()->fromPost('data', null);
        $actionToApply = $this->params()->fromPost('actionToApply', null);
        $amountToApply = $this->params()->fromPost('amountToApply', null);
        if ((empty($data)) || (empty($actionToApply)) || (empty($amountToApply))) {
            $this->getResponse()->setStatusCode(500);
            return $this->_returnJson(array(
                'success' => false,
                "msg" => 'Missing parameters'
            ));
        }
        $updateData = Json::decode($data, Json::TYPE_ARRAY);
        if (!is_array($updateData)) {
            $this->getResponse()->setStatusCode(500);
            return $this->_returnJson(array(
                'success' => false,
                "msg" => 'Not an array'
            ));
        }
        if ($actionToApply == "add") {
            $result = Manager::getService("Stock")->increaseStock($updateData['productId'], $updateData['id'], $amountToApply);
        } else {
            $result = Manager::getService("Stock")->decreaseStock($updateData['productId'], $updateData['id'], $amountToApply);
        }
        if (!$result['success']) {
            return $this->_returnJson($result);
        }
        $updateData['stock'] = $result['newStock'];
        return $this->_returnJson(array(
            'success' => true,
            "data" => $updateData
        ));
    }

    public function exportAction()
    {
        $params = $this->params()->fromQuery();
        $filters = Filter::factory();
        if (!empty($params['startDate'])) {
            $filters->addFilter(
                Filter::factory('OperatorTovalue')->setName('createTime')
                    ->setOperator('$gte')
                    ->setValue((int)$params['startDate'])
            );
        }
        if (!empty($params['endDate'])) {
            $filters->addFilter(
                Filter::factory('OperatorTovalue')->setName('createTime')
                    ->setOperator('$lte')
                    ->setValue((int)$params['endDate'])
            );
        }
        $contentType = Manager::getService("ContentTypes")->findById($params['typeId']);
        $filters->addFilter(
            Filter::factory('Value')->setName('typeId')
                ->setValue($params['typeId'])
        );
        $contents = $this->_dataService->getOnlineList($filters);
        $fileName = 'export_rubedo_contents_' . $contentType['type'] . '_' . time() . '.csv';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        $csvResource = fopen($filePath, 'w+');
        $fieldsArray = array(
            "text" => null,
            "summary" => null
        );
        $headerArray = array(
            "text" => "Title",
            "summary" => "Summary"
        );
        $fieldsArray["createTime"] = null;
        $multivaluedFieldsArray = array();
        $headerArray["createTime"] = "Creation";
        $exportableFieldTypes = [
            "Ext.form.field.Text",
            "textfield",
            "Ext.form.field.TextArea",
            "textarea",
            "textareafield",
            "Ext.form.field.Number",
            "numberfield",
            "Ext.form.field.ComboBox",
            "combobox",
            "Ext.form.field.Checkbox",
            "checkboxfield",
            "Ext.form.RadioGroup",
            "radiogroup",
            "Ext.form.field.Date",
            "datefield",
            "Ext.form.field.Time",
            "timefield",
            "Ext.slider.Single",
            "slider",
            "Rubedo.view.CKEField",
            "CKEField",
        ];
        foreach ($contentType['fields'] as $typeField) {
            if (in_array($typeField['cType'], $exportableFieldTypes)) {
                $fieldsArray[$typeField['config']['name']] = $typeField['cType'];
                $headerArray[$typeField['config']['name']] = $typeField['config']['fieldLabel'];
                if (isset($typeField['config']['multivalued']) && $typeField['config']['multivalued']) {
                    $multivaluedFieldsArray[] = $typeField['config']['name'];
                }
            }
        }
        $taxoService = Manager::getService("Taxonomy");
        $taxoTermsService = Manager::getService("TaxonomyTerms");
        $taxoHeaderArray = array();
        $taxoFieldsArray = array();
        foreach ($contentType['vocabularies'] as $vocabId) {
            if (!empty($vocabId) && $vocabId != "navigation") {
                $vocabulary = $taxoService->findById($vocabId);
                if ($vocabulary) {
                    $taxoHeaderArray[$vocabId] = $vocabulary['name'];
                    $taxoFieldsArray[] = $vocabId;
                }
            }
        }
        $csvLine = array();
        foreach ($fieldsArray as $field => $fieldType) {
            $csvLine[] = $headerArray[$field];
        }
        foreach ($taxoFieldsArray as $field) {
            $csvLine[] = $taxoHeaderArray[$field];
        }
        fputcsv($csvResource, $csvLine, ';');

        foreach ($contents['data'] as $content) {
            $csvLine = array();
            foreach ($fieldsArray as $field => $fieldType) {
                switch ($field) {
                    case 'createTime':
                        $csvLine[] = date('d-m-Y H:i:s', $content["createTime"]);
                        break;
                    case 'text':
                        $csvLine[] = isset($content[$field]) ? $content[$field] : '';
                        break;
                    default:
                        if (!isset($content['fields'][$field])) {
                            $csvLine[] = '';
                        } elseif (in_array($field, $multivaluedFieldsArray) && is_array($content['fields'][$field])) {
                            $formatedValuesArray = array();
                            foreach ($content['fields'][$field] as $unformatedValue) {
                                $formatedValuesArray[] = $this->formatFieldData($unformatedValue, $fieldType);
                            }
                            $csvLine[] = implode(", ", $formatedValuesArray);
                        } else {
                            $csvLine[] = $this->formatFieldData($content['fields'][$field], $fieldType);
                        }
                        break;
                }
            }
            foreach ($taxoFieldsArray as $taxoField) {
                if (!isset($content['taxonomy'][$taxoField])) {
                    $csvLine[] = '';
                } elseif (is_array($content['taxonomy'][$taxoField])) {
                    $termLabelsArray = array();
                    foreach ($content['taxonomy'][$taxoField] as $taxoTermId) {
                        if (!empty($taxoTermId)) {
                            $foundTerm = $taxoTermsService->findById($taxoTermId);
                            if ($foundTerm) {
                                $termLabelsArray[] = $foundTerm['text'];
                            }
                        }
                    }
                    $csvLine[] = implode(", ", $termLabelsArray);
                } else {
                    if (!empty($content['taxonomy'][$taxoField])) {
                        $foundTerm = $taxoTermsService->findById($content['taxonomy'][$taxoField]);
                        if ($foundTerm) {
                            $csvLine[] = $foundTerm['text'];
                        } else {
                            $csvLine[] = '';
                        }
                    } else {
                        $csvLine[] = '';
                    }
                }
            }
            fputcsv($csvResource, $csvLine, ';');
        }
        $content = file_get_contents($filePath);
        $response = $this->getResponse();
        $headers = $response->getHeaders();
        $headers->addHeaderLine('Content-Type', 'text/csv');
        $headers->addHeaderLine('Content-Disposition', "attachment; filename=\"$fileName\"");
        $headers->addHeaderLine('Accept-Ranges', 'bytes');
        $headers->addHeaderLine('Content-Length', strlen($content));
        $response->setContent($content);
        return $response;
    }

    protected function formatFieldData($value, $cType = null)
    {
        switch ($cType) {
            case 'Ext.form.field.Date':
            case 'datefield':
                return date('d-m-Y H:i:s', $value);
                break;
            case 'Ext.form.RadioGroup':
            case 'radiogroup':
            case 'Ext.form.field.ComboBox':
            case 'combobox':
                if (is_array($value)) {
                    return implode(", ", $value);
                } else {
                    return $value;
                }
                break;
            default:
                return ($value);
                break;
        }
    }

}
