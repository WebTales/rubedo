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
use Zend\Json\Json;
use Zend\View\Model\JsonModel;

/**
 * Controller providing CRUD API for the users JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class UsersController extends DataAccessController
{

    public function __construct()
    {
        parent::__construct();

        // init the data access service
        $this->_dataService = Manager::getService('Users');
    }

    public function changePasswordAction()
    {
        $password = $this->params()->fromPost('password');
        $id = $this->params()->fromPost('id');
        $version = $this->params()->fromPost('version');

        if (!empty($password) && !empty($id) && !empty($version)) {

            $result = $this->_dataService->changePassword($password, $version, $id);

            if ($result == true) {
                $message['success'] = true;
            } else {
                $message['success'] = false;
            }

            return new JsonModel($message);
        } else {
            $returnArray = array(
                'success' => false,
                "msg" => 'No Data'
            );
        }

        if (!$returnArray['success']) {
            $this->getResponse()->setStatusCode(500);
        }

        return new JsonModel($returnArray);
    }

    //extends update method to explicitly reindex user
    public function updateAction()
    {
        $data = $this->params()->fromPost('data');

        if (!is_null($data)) {
            $updateData = Json::decode($data, Json::TYPE_ARRAY);
            if (is_array($updateData)) {
                $options = array();
                $options['reindexUser'] = true;
                $returnArray = $this->_dataService->update($updateData, $options);
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
        $userType = Manager::getService("UserTypes")->findById($params['typeId']);
        $filters->addFilter(
            Filter::factory('Value')->setName('typeId')
                ->setValue($params['typeId'])
        );
        $users = $this->_dataService->getList($filters);
        $fileName = 'export_rubedo_users_' . $userType['type'] . '_' . time() . '.csv';
        $filePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $fileName;
        $csvResource = fopen($filePath, 'w+');
        $fieldsArray = array(
            "email" => null,
            "name" => null
        );
        $headerArray = array(
            "email" => "Email",
            "name" => "Name"
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
            "Rubedo.view.localiserField",
            "localiserField",
        ];
        foreach ($userType['fields'] as $typeField) {
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
        foreach ($userType['vocabularies'] as $vocabId) {
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

        foreach ($users['data'] as $user) {
            $csvLine = array();
            foreach ($fieldsArray as $field => $fieldType) {
                switch ($field) {
                    case 'createTime':
                        $csvLine[] = date('d-m-Y H:i:s', $user["createTime"]);
                        break;
                    case 'email':
                    case 'name':
                        $csvLine[] = isset($user[$field]) ? $user[$field] : '';
                        break;
                    default:
                        if (!isset($user['fields'][$field])) {
                            $csvLine[] = '';
                        } elseif (in_array($field, $multivaluedFieldsArray) && is_array($user['fields'][$field])) {
                            $formatedValuesArray = array();
                            foreach ($user['fields'][$field] as $unformatedValue) {
                                $formatedValuesArray[] = $this->formatFieldData($unformatedValue, $fieldType);
                            }
                            $csvLine[] = implode(", ", $formatedValuesArray);
                        } else {
                            $csvLine[] = $this->formatFieldData($user['fields'][$field], $fieldType);
                        }
                        break;
                }
            }
            foreach ($taxoFieldsArray as $taxoField) {
                if (!isset($user['taxonomy'][$taxoField])) {
                    $csvLine[] = '';
                } elseif (is_array($user['taxonomy'][$taxoField])) {
                    $termLabelsArray = array();
                    foreach ($user['taxonomy'][$taxoField] as $taxoTermId) {
                        if (!empty($taxoTermId)) {
                            $foundTerm = $taxoTermsService->findById($taxoTermId);
                            if ($foundTerm) {
                                $termLabelsArray[] = $foundTerm['text'];
                            }
                        }
                    }
                    $csvLine[] = implode(", ", $termLabelsArray);
                } else {
                    if (!empty($user['taxonomy'][$taxoField])) {
                        $foundTerm = $taxoTermsService->findById($user['taxonomy'][$taxoField]);
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
            case 'localiserField':
            case 'Rubedo.view.localiserField':
                if (isset($value["lat"])&&isset($value["lon"])){
                    $stringLat= (string) $value["lat"];
                    $stringLon= (string) $value["lon"];
                    return($stringLat.",".$stringLon);
                } else {
                    return "";
                }
                break;
            default:
                if (is_array($value)){
                    return "";
                }
                return ($value);
                break;
        }
    }
}
