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
require_once ('AbstractController.php');

/**
 *
 *
 *
 *
 *
 * Block to display front office language menu
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class LanguageMenuController extends AbstractController
{

    public function indexAction ()
    {
        $output = $this->params()->fromQuery();
        
        if (isset($output['block-config']['displayAs'])) {
            switch ($output['block-config']['displayAs']) {
                case "menu":
                    $displayType = 'menu';
                    break;
                case "select":
                default:
                    $displayType = 'combo';
                    break;
            }
        } else {
            $displayType = 'combo';
        }
        
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/language-menu/" . $displayType . ".html.twig");
        
        $currentLocale = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        
        $output['currentLanguage'] = Manager::getService('Languages')->findByLocale($currentLocale);
        
        $site = $this->params()->fromQuery('site');
        if (isset($site['languages'])) {
            $filters = Filter::factory();
            $filters->addFilter(Filter::factory('In')->setName('locale')
                ->setValue($site['languages']));
            $filters->addFilter(Filter::factory('Value')->setName('active')
                ->setValue(true));
            $languageResult = Manager::getService('Languages')->getList($filters, array(
                array(
                    'property' => 'label',
                    'direction' => 'ASC'
                )
            ));
            $output['languages'] = $languageResult['data'];
            $output['showCurrentLanguage'] = $output['block-config']['showCurrentLanguage'];
        }
        
        $css = array();
        $js = array(
            $this->getRequest()->getBasePath() . '/' . Manager::getService('FrontOfficeTemplates')->getFileThemePath("js/language.js")
        );
        
        return $this->_sendResponse($output, $template, $css, $js);
    }
}
