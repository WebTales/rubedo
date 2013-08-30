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
namespace Rubedo\Blocks\Controller;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\View\Model\JsonModel;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class CalendarController extends ContentListController
{

    protected $_defaultTemplate = 'calendar';

    public function indexAction ()
    {
        $output = $this->_getList();
        $blockConfig = $this->params()->fromQuery('block-config');
        
        if (isset($blockConfig['displayType']) && ! empty($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        $css = array();
        $js = array(
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/calendar.js")
        );
        return $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getList ()
    {
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->params()->fromQuery('block-config');
        
        $dateField = isset($blockConfig['dateField']) ? $blockConfig['dateField'] : $this->params()->fromQuery('date-field', 'date');
        //$endDateField = isset($blockConfig['endDateField']) ? $blockConfig['endDateField'] : $this->params()->fromQuery('endDateField', 'date_end');
        $usedDateField = 'fields.' . $dateField;
        
        $date = $this->params()->fromQuery('cal-date');
        if ($date) {
            list ($month, $year) = explode('-', $date);
        } else {
            $timestamp = Manager::getService('CurrentTime')->getCurrentTime();
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
        }
        $month = intval($month);
        $year = intval($year);
        $date = (string) $month . '-' . (string) $year;
        
        $timestamp = (string) mktime(0, 0, 0, $month, 1, $year); // cast to string as date are stored as text in DB
        $nextMonth = new \DateTime();
        $nextMonth->setTimestamp($timestamp);
        $nextMonth->add(new \DateInterval('P1M'));
        $nextMonthTimeStamp = (string) $nextMonth->getTimestamp(); // cast to string as date are stored as text in DB
        
        $queryId = $this->params()->fromQuery('query-id', $blockConfig['query']);
        $data = array();
        $filledDate = array();
        
        if ($queryId) { // nothing shown if no query given
            $queryFilter = Manager::getService('Queries')->getFilterArrayById($queryId);
            
            $queryType = $queryFilter["queryType"];
            
            $dateFilter = Filter::factory('And')->addFilter(Filter::factory('OperatorTovalue')->setName($usedDateField)
                ->setOperator('$gte')
                ->setValue($timestamp))
                ->addFilter(Filter::factory('OperatorTovalue')->setName($usedDateField)
                ->setOperator('$lt')
                ->setValue($nextMonthTimeStamp));
            
            $queryFilter['filter']->addFilter($dateFilter);
            
            $queryId = $this->params()->fromQuery('query-id', $blockConfig['query']);
            
            $query = $this->_queryReader->getQueryById($queryId);
            
            if ($queryType === "manual" && $query != false && isset($query['query']) && is_array($query['query'])) {
                $contentOrder = $query['query'];
                $keyOrder = array();
                $contentArray = array();
                $contentArray['data'] = array();
                
                // getList
                $unorderedContentArray = $this->getContentList($queryFilter, array(
                    'limit' => 100,
                    'currentPage' => 1,
                    'skip' => 0
                ));
                
                foreach ($contentOrder as $value) {
                    foreach ($unorderedContentArray['data'] as $subKey => $subValue) {
                        if ($value === $subValue['id']) {
                            $keyOrder[] = $subKey;
                        }
                    }
                }
                
                foreach ($keyOrder as $value) {
                    $contentArray["data"][] = $unorderedContentArray["data"][$value];
                }
            } else {
                
                $contentArray = $this->getContentList($queryFilter, array(
                    'limit' => 100,
                    'currentPage' => 1,
                    'skip' => 0
                ));
            }
            
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $fields['typeId'] = $vignette['typeId'];
                
                if(!is_array($vignette["fields"]["date"])){
                    $fields['readDate'] = Manager::getService('Date')->getLocalised(null, $vignette['fields'][$dateField]);
                } else {
                    $fields['readDate'] = Manager::getService('Date')->getLocalised(null, $vignette['fields'][$dateField][0]);
                }
                
                $data[] = $fields;
                
                if(!is_array($vignette["fields"]["date"])){
                    $day = date('d', $vignette["fields"]["date"]);
                    $filledDate[$day] = true;
                } else {
                    foreach ($vignette["fields"]["date"] as $value) {
                        $day = date('d', $value);
                        $filledDate[$day] = true;
                    }
                }
                
                //$filledDate[date('d', $vignette['fields'][$dateField])] = true;
            }
        } else {}
        
        $output = $this->params()->fromQuery();
        $output['blockConfig'] = $blockConfig;
        $output["data"] = $data;
        $output["query"]['type'] = isset($queryType) ? $queryType : null;
        $output["query"]['id'] = isset($queryId) ? $queryId : null;
        $output['prefix'] = $this->param()->fromQuery('prefix');
        $output['filledDate'] = $filledDate;
        $output['days'] = Manager::getService('Date')->getShortDayList();
        $output['month'] = Manager::getService('Date')->getLocalised('MMMM', $timestamp);
        $output['year'] = Manager::getService('Date')->getLocalised('y', $timestamp);
        if (intval($month) == 12) {
            $output['nextDate'] = '1-' . (string) ($year + 1);
        } else {
            $output['nextDate'] = (string) ($month + 1) . '-' . (string) $year;
        }
        
        if (intval($month) == 1) {
            $output['prevDate'] = '12-' . (string) ($year - 1);
        } else {
            $output['prevDate'] = (string) ($month - 1) . '-' . (string) $year;
        }
        $output['display'] = array();
        if (isset($blockConfig['display'])) {
            foreach ($blockConfig['display'] as $value) {
                $output['display'][$value] = true;
            }
        }
        
        $singlePage = isset($blockConfig['singlePage']) ? $blockConfig['singlePage'] : $this->params()->fromQuery('current-page');
        
        $output['singlePage'] = $this->params()->fromQuery('single-page', $singlePage);
        
        $output['monthArray'] = Manager::getService('Date')->getMonthArray($timestamp);
        
        $output['caldate'] = $date;
        
        $output['dateField'] = $dateField;
        
        return $output;
    }

    public function xhrGetCalendarAction ()
    {
        $twigVars = $this->_getList();
        
        $calendarHtml = Manager::getService('FrontOfficeTemplates')->render($template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/calendar/table.html.twig"), $twigVars);
        $html = Manager::getService('FrontOfficeTemplates')->render($template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/calendar/list.html.twig"), $twigVars);
        
        $data = array(
            'calendarHtml' => $calendarHtml,
            'html' => $html
        );
        return new JsonModel($data);
    }
}
