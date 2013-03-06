<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    yet to be written
 * @version    $Id:
 */
Use Rubedo\Services\Manager;

require_once ('ContentListController.php');

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_CalendarController extends Blocks_ContentListController
{

    protected $_defaultTemplate = 'calendar';

    public function indexAction ()
    {
        $output = $this->_getList();
        $blockConfig = $this->getRequest()->getParam('block-config');
        
        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $this->_defaultTemplate . ".html.twig");
        }
        $css = array();
        $js = array(
            '/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/calendar.js")
        );
        $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _getList ()
    {
        
        
        
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->getRequest()->getParam('block-config');
        
        $dateField = isset($blockConfig['dateField'])?$blockConfig['dateField']:$this->getParam('date-field', 'date');
        $endDateField = isset($blockConfig['endDateField'])?$blockConfig['endDateField']:$this->getParam('endDateField', 'date_end');
        $usedDateField = 'fields.' . $dateField;
        
        $date = $this->getParam('cal-date');
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
        
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        $nextMonth = new DateTime();
        $nextMonth->setTimestamp($timestamp);
        $nextMonth->add(new DateInterval('P1M'));
        $nextMonthTimeStamp = $nextMonth->getTimestamp();
        
        $queryId = $this->getParam('query-id', $blockConfig['query']);
        $data = array();
        $filledDate = array();
        
        if ($queryId) { //nothing shown if no query given
            $queryConfig = $this->getQuery($queryId);
            $queryType = $queryConfig['type'];
            $queryFilter = $this->setFilters($queryConfig);
            
            $condition = array(
                '$gte' => "$timestamp",
                '$lt' => "$nextMonthTimeStamp"
            );
            $queryFilter['filter'][] = array(
                'property' => $usedDateField,
                'value' => $condition
            );
            
            $contentArray = $this->getContentList($queryFilter, array(
                'limit' => 100,
                'currentPage' => 1
            ));
            
            
            foreach ($contentArray['data'] as $vignette) {
                $fields = $vignette['fields'];
                $fields['title'] = $fields['text'];
                unset($fields['text']);
                $fields['id'] = (string) $vignette['id'];
                $fields['typeId'] = $vignette['typeId'];
                $fields['readDate'] = Manager::getService('Date')->getLocalised('%A %e %B %Y', $vignette['fields'][$dateField]);
                $data[] = $fields;
                $filledDate[intval(date('d', $vignette['fields'][$dateField]))] = true;
            }
        } else {}
        
        $output = $this->getAllParams();
        $output['blockConfig'] = $blockConfig;
        $output["data"] = $data;
        $output["query"]['type'] = isset($queryType)?$queryType:null;
        $output["query"]['id'] = isset($queryId)?$queryId:null;
        $output['prefix'] = $this->getRequest()->getParam('prefix');
        $output['filledDate'] = $filledDate;
        $output['days'] = Manager::getService('Date')->getShortDayList();
        $output['month'] = Manager::getService('Date')->getLocalised('%B', $timestamp);
        $output['year'] = Manager::getService('Date')->getLocalised('%Y', $timestamp);
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
        
        $singlePage = isset($blockConfig['singlePage']) ? $blockConfig['singlePage'] : $this->getParam('current-page');
        // var_dump($this->getParam('current-page'));die();
        
        $output['singlePage'] = $this->getParam('single-page', $singlePage);
        
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
        $this->_helper->json($data);
    }
}
