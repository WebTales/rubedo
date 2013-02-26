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
class Blocks_AgendaController extends Blocks_ContentListController
{
    protected $_defaultTemplate = 'agenda';
    
    public function indexAction ()
    {
        $usedDateField = 'createTime';
        
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');
        $this->_queryReader = Manager::getService('Queries');
        $blockConfig = $this->getRequest()->getParam('block-config');
        
        $date = $this->getParam('cal-date');
        if($date){
            list($month,$year) = explode('-', $date);
            
        }else{
            $timestamp = Manager::getService('CurrentTime')->getCurrentTime();
            $year = date('Y',$timestamp);
            $month = date('m',$timestamp);
        }
        $timestamp = mktime(0,0,0,$month,1,$year);
        $nextMonth = new DateTime();
        $nextMonth->setTimestamp($timestamp);
        $nextMonth->add(new DateInterval('P1M'));
        $nextMonthTimeStamp = $nextMonth->getTimestamp();
        
        $queryId = $blockConfig['query'];
        $queryConfig = $this->getQuery($queryId);
        $queryType=$queryConfig['type'];
        $queryFilter = $this->setFilters($queryConfig);
        
        $condition = array('$gte'=>$timestamp,'$lt'=>$nextMonthTimeStamp);
        $queryFilter['filter'][]=array('property'=>$usedDateField,'value'=>$condition);

        $contentArray = $this->getContentList($queryFilter,array('limit'=>100,'currentPage'=>1));
        
        $filledDate = array();
        $data = array();
        foreach ($contentArray['data'] as $vignette) {
            //$vignette['readDate'] = date('c',$vignette['createTime']);
            //var_dump($vignette['readDate']);
            
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string) $vignette['id'];
            $fields['typeId'] = $vignette['typeId'];
            $data[] = $fields;
            $filledDate[intval(date('d',$vignette[$usedDateField]))]=true;
        }
        
        $output['blockConfig'] = $blockConfig;
        $output["data"] = $data;
        $output["query"]['type'] = $queryType;
        $output["query"]['id'] = $queryId;
        $output['prefix'] = $this->getRequest()->getParam('prefix');
        $output['filledDate'] = $filledDate;
        $output['days'] = Manager::getService('Date')->getShortDayList();
        $output['month'] = Manager::getService('Date')->getLocalised('%B', 
                $timestamp);
        $output['year'] = Manager::getService('Date')->getLocalised('%Y', 
                $timestamp);
        if (intval($month) == 12) {
            $output['nextDate'] = '01-' . (string) ($year + 1);
        } else {
            $output['nextDate'] = (string) ($month + 1) . '-' . (string) $year;
        }
        
        if (intval($month) == 1) {
            $output['prevDate'] = '12-' . (string) ($year - 1);
        } else {
            $output['prevDate'] = (string) ($month - 1) . '-' . (string) $year;
        }
        
        $output['monthArray'] = Manager::getService('Date')->getMonthArray(
                $timestamp);
            	

        if (isset($blockConfig['displayType'])) {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/" . $blockConfig['displayType'] . ".html.twig");
        } else {
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/".$this->_defaultTemplate.".html.twig");
        }
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }
    
}
