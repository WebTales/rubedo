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
use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Templates\Raw\RawViewModel;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class AbstractController extends AbstractActionController
{

    protected $_workspace;

    public function init ()
    {
        $templateService = Manager::getService('FrontOfficeTemplates');
        Rubedo\Collection\AbstractCollection::setIsFrontEnd(true);
        
        // handle preview for ajax request, only if user is a backoffice user
        if (Manager::getService('Acl')->hasAccess('ui.backoffice')) {
            $isDraft = $this->getParam('is-draft', false);
            if (! is_null($isDraft)) {
                Zend_Registry::set('draft', $isDraft);
            }
        }
        
        // get current page property
        $this->currentPage = $this->getParam('current-page');
        
        $currentPage = Manager::getService('Pages')->findById($this->currentPage);
        
        if (is_null($currentPage)) {
            throw new Rubedo\Exceptions\Access('You can not access this page.', "Exception15");
        } else {
            Manager::getService('PageContent')->setCurrentPage($currentPage['id']);
        }
        $this->siteId = $currentPage['site'];
        
        if ($this->getRequest()->isXmlHttpRequest()) {
            // init browser languages
            $zend_locale = new Zend_Locale(Zend_Locale::BROWSER);
            $browserLanguages = array_keys($zend_locale->getBrowser());
            
            $cookieValue = $this->getRequest()->getCookie('locale');
            Manager::getService('CurrentLocalization')->resolveLocalization($currentPage['site'], null, $browserLanguages, $cookieValue);
        }
        
        if (! $templateService->themeHadBeenSet()) {
            $currentSite = Manager::getService('Sites')->findById($currentPage['site']);
            $theme = $currentSite['theme'];
            $templateService->setCurrentTheme($theme);
        }
        
        // set current workspace
        $this->_workspace = $currentPage['workspace'];
    }

    /**
     * handle the response weither it is a direct call or a partial call
     *
     * if direct HTTP request, it render templates
     * if it is a sub call from Rubedo, return the data for global rendering
     *
     * @param array $output
     *            data to be rendered
     * @param string $template
     *            twig template to be used
     * @param array $css
     *            array of CSS that should be included
     * @param array $js
     *            array of JS that should be included
     */
    protected function _sendResponse (array $output, $template, array $css = null, array $js = null)
    {
        $output['classHtml'] = $this->params()->fromQuery('classHtml', '');
        $output['idHtml'] = $this->params()->fromQuery('idHtml', '');
        
        $output['lang'] = Manager::getService('CurrentLocalization')->getCurrentLocalization();
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_servicePage = Manager::getService('PageContent');
        
        if (is_array($css)) {
            foreach ($css as $value) {
                $this->_servicePage->appendCss($value);
            }
        }
        if (is_array($js)) {
            foreach ($js as $value) {
                $this->_servicePage->appendJs($value);
            }
        }
        
        $viewModel = new RawViewModel($output);
        $viewModel->setTemplate($template);
        $viewModel->setTerminal(true);
        return $viewModel;
    }
}
