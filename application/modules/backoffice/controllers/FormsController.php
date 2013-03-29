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
Use Rubedo\Services\Manager;

require_once ('DataAccessController.php');

/**
 * Controller providing CRUD API for the Forms JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class Backoffice_FormsController extends Backoffice_DataAccessController
{

    /**
     * Array with the read only actions
     */
    protected $_readOnlyAction = array(
        'index',
        'find-one',
        'read-child',
        'tree',
        'model',
        'get-csv',
        'get-stats'
    );

    public function init ()
    {
        parent::init();
        
        // init the data access service
        $this->_dataService = Rubedo\Services\Manager::getService('Forms');
    }

    public function getStatsAction ()
    {
        $formId = $this->getParam('form-id');
        if (! $formId) {
            throw new Rubedo\Exceptions\User('pas de formulaire en argument');
        }
        $statsResponse = array();
        $statsResponse['validResults'] = Manager::getService('FormsResponses')->countValidResponsesByFormId($formId);
        $statsResponse['invalidResults'] = Manager::getService('FormsResponses')->countInvalidResponsesByFormId($formId);
        $statsResponse['totalResults'] = $statsResponse['invalidResults'] + $statsResponse['validResults'];
        $response = array();
        $response['data'] = $statsResponse;
        $response['success'] = true;
        $this->_helper->json($response);
    }

    public function getCsvAction ()
    {
        $formId = $this->getParam('form-id');
        if (! $formId) {
            throw new Rubedo\Exceptions\User('pas de formulaire en argument');
        }
        
        $form = Manager::getService('Forms')->findById($formId);
        
        $displayQnb = $this->getParam('display-qnb', false);
        $fileTitle = $this->_filterName($form['title']);
        
        $fileName = $fileTitle . '_' . $formId . '_' . date('Ymd') . '.csv';
        $filePath = sys_get_temp_dir() . '/' . $fileName;
        $csvResource = fopen($filePath, 'w+');
        
        $fieldsArray = array();
        
        $responsePages = array();
        $headerArray = array(
            'Date',
            'Terminé'
        );
        $definiedAnswersArray = array();
        
        foreach ($form['formPages'] as $page) {
            foreach ($page['elements'] as $element) {
                switch ($element['itemConfig']['fType']) {
                    case 'multiChoiceQuestion':
                        if ($element['itemConfig']['fieldType'] == 'checkboxgroup') {
                            $tempSubField = array();
                            foreach ($element['itemConfig']['fieldConfig']['items'] as $item) {
                                $headerArray[] = ($displayQnb ? $element['itemConfig']["qNb"] . ' - ' : '') . $element['itemConfig']["label"] . ' - ' . $item['boxLabel'];
                                $tempSubField[] = $item['inputValue'];
                                $definiedAnswersArray[$item['inputValue']] = $item['boxLabel'];
                            }
                            $fieldsArray[] = array(
                                'type' => 'qcm',
                                'value' => array(
                                    'id' => $element['id'],
                                    'items' => $tempSubField
                                )
                            );
                            break;
                        } else {
                            $headerArray[] = ($displayQnb ? $element['itemConfig']["qNb"] . ' - ' : '') . $element['itemConfig']["label"];
                            $fieldsArray[] = array(
                                'type' => 'simple',
                                'value' => $element['id']
                            );
                            foreach ($element['itemConfig']['fieldConfig']['items'] as $item) {
                                $definiedAnswersArray[$item['inputValue']] = $item['boxLabel'];
                            }
                            break;
                        }
                    case 'openQuestion':
                        $headerArray[] = ($displayQnb ? $element['itemConfig']["qNb"] . ' - ' : '') . $element['itemConfig']["label"];
                        $fieldsArray[] = array(
                            'type' => 'open',
                            'value' => $element['id']
                        );
                        break;
                    default:
                        break;
                }
            }
        }
        
        $list = Manager::getService('FormsResponses')->getResponsesByFormId($formId);
        
        fputcsv($csvResource, $headerArray, ';');
        
        foreach ($list['data'] as $response) {
            $csvLine = array(
                Manager::getService('Date')->getLocalised(null, $response['lastUpdateTime']),
                $response['status']=='finished'
            );
            foreach ($fieldsArray as $element) {
                switch ($element['type']) {
                    case 'open':
                        $csvLine[] = isset($response['data'][$element['value']])?$response['data'][$element['value']]:null;
                        break;
                    case 'simple':
                        if(isset($response['data'][$element['value']]) && is_array($response['data'][$element['value']])){
                            $result = array_pop($response['data'][$element['value']]);
                            $csvLine[] = $definiedAnswersArray[$result];
                        }else{
                            $csvLine[] = null;
                        }
                        
                        
                        break;
                    case 'qcm':
                        foreach ($element['value']['items'] as $item) {
                            if (isset($response['data'][$element['value']['id']])){
                                $csvLine[] = in_array($item, $response['data'][$element['value']['id']]);
                            }else{
                                $csvLine[] = null;
                            }
                        }
                        break;
                    default:
                        break;
                }
            }
            
            fputcsv($csvResource, $csvLine, ';');
        }
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $this->getResponse()->clearBody();
        $this->getResponse()->clearHeaders();
        $this->getResponse()->setHeader('Content-Type', 'application/csv');
        $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $this->getResponse()->sendHeaders();
        
        fclose($csvResource);
        
        $content = file_get_contents($filePath);
        echo utf8_decode($content);
        die();
    }

    protected function _filterName ($url)
    {
        mb_regex_encoding('UTF-8');
        
        $normalizeChars = array(
            'Á' => 'A',
            'À' => 'A',
            'Â' => 'A',
            'Ã' => 'A',
            'Å' => 'A',
            'Ä' => 'A',
            'Æ' => 'AE',
            'Ç' => 'C',
            'É' => 'E',
            'È' => 'E',
            'Ê' => 'E',
            'Ë' => 'E',
            'Í' => 'I',
            'Ì' => 'I',
            'Î' => 'I',
            'Ï' => 'I',
            'Ð' => 'Eth',
            'Ñ' => 'N',
            'Ó' => 'O',
            'Ò' => 'O',
            'Ô' => 'O',
            'Õ' => 'O',
            'Ö' => 'O',
            'Ø' => 'O',
            'Ú' => 'U',
            'Ù' => 'U',
            'Û' => 'U',
            'Ü' => 'U',
            'Ý' => 'Y',
            
            'á' => 'a',
            'à' => 'a',
            'â' => 'a',
            'ã' => 'a',
            'å' => 'a',
            'ä' => 'a',
            'æ' => 'ae',
            'ç' => 'c',
            'é' => 'e',
            'è' => 'e',
            'ê' => 'e',
            'ë' => 'e',
            'í' => 'i',
            'ì' => 'i',
            'î' => 'i',
            'ï' => 'i',
            'ð' => 'eth',
            'ñ' => 'n',
            'ó' => 'o',
            'ò' => 'o',
            'ô' => 'o',
            'õ' => 'o',
            'ö' => 'o',
            'ø' => 'o',
            'ú' => 'u',
            'ù' => 'u',
            'û' => 'u',
            'ü' => 'u',
            'ý' => 'y',
            
            'ß' => 'sz',
            'þ' => 'thorn',
            'ÿ' => 'y',
            ' ' => '-',
            '\'' => '-'
        );
        
        $url = strtr(trim($url), $normalizeChars);
        $url = mb_strtolower($url, 'UTF-8');
        $url = mb_ereg_replace("[^A-Za-z0-9\\.\\-]", "", $url);
        $url = trim($url, '-');
        
        return $url;
    }
}