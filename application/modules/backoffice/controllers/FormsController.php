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
            throw new Rubedo\Exceptions\User('This action needs a form id as argument.', "Exception11");
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
            throw new Rubedo\Exceptions\User('This action needs a form id as argument.', "Exception11");
        }
        
        $form = Manager::getService('Forms')->findById($formId);
        
        $displayQnb = $this->getParam('display-qnb', false);
        $fileTitle = Manager::getService('Pages')->filterUrl($form['title']); // $this->_filterName($form['title']);
        
        $fileName = $fileTitle . '_' . $formId . '_' . date('Ymd') . '.csv';
        $filePath = sys_get_temp_dir() . '/' . $fileName;
        $csvResource = fopen($filePath, 'w+');
        
        $fieldsArray = array();
        
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
                        
                        }else {
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
                    case 'predefinedPrefsQuestion' :
                            for ($i = 1; $i <= $element['itemConfig']['numberOfQuestions']; $i++) {
                                $headerArray[]=$element['itemConfig']["qNb"]." - question ".$i." - ligne du plan d'expérience";
                                $fieldsArray[] = array(
                                    'type' => 'add1',
                                    'value' => $element['id']."question".$i."expPlanRow"
                                );
                                for ($j = 1; $j <= $element['itemConfig']['numberOfChoices']; $j++) {
                                    $headerArray[]=$element['itemConfig']["qNb"]." - question ".$i." - choix ".$j;
                                    $fieldsArray[] = array(
                                        'type' => 'open',
                                        'value' => $element['id']."question".$i."choice".$j
                                    );
                                }
                            }
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
                Manager::getService('Date')->getDefaultDatetime($response['lastUpdateTime']),
                $response['status'] == 'finished' ? 'oui' : 'non'
            );
            foreach ($fieldsArray as $element) {
                switch ($element['type']) {
                    case 'open':
                        $csvLine[] = isset($response['data'][$element['value']]) ? $response['data'][$element['value']] : null;
                        break;
                    case 'add1':
                        $csvLine[] = isset($response['data'][$element['value']]) ? $response['data'][$element['value']]+1 : null;
                        break;
                    case 'simple':
                        if (isset($response['data'][$element['value']]) && is_array($response['data'][$element['value']])) {
                            $result = array_pop($response['data'][$element['value']]);
                            $csvLine[] = $definiedAnswersArray[$result];
                        } else {
                            $csvLine[] = null;
                        }
                        
                        break;
                    case 'qcm':
                        foreach ($element['value']['items'] as $item) {
                            if (isset($response['data'][$element['value']['id']])) {
                                $csvLine[] = in_array($item, $response['data'][$element['value']['id']]);
                            } else {
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
}