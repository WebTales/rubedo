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
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;

require_once ('AbstractController.php');

/**
 *
 * Block to display front office language menu
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_LanguageMenuController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $output = $this->getAllParams();
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/language-menu.html.twig");

        $currentLocale =  Manager::getService('CurrentLocalization')->getCurrentLocalization();
        
        $output['currentLanguage'] = Manager::getService('Languages')->findByLocale($currentLocale);
        
        $site = $this->getParam('site');
        if(isset($site['languages'])){
            $filters = Filter::factory();
            $filters->addFilter(Filter::factory('In')->setName('locale')->setValue($site['languages']));
            $filters->addFilter(Filter::factory('Value')->setName('active')->setValue(true));
            $languageResult =  Manager::getService('Languages')->getList($filters);
            $output['languages'] = $languageResult['data'];
        }     
        
        $css = array();
        $js = array('/templates/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/language.js"));
        $this->_sendResponse($output, $template, $css, $js);
    }
}
