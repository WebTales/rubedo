<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
Use Rubedo\Services\Manager;

/**
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
abstract class Blocks_AbstractController extends Zend_Controller_Action
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
        $this->_sendResponse($output, $template, $css, $js);
    }


    protected function _sendResponse ($output, $template, array $css = null, array $js = null)
    {
        $output['classHtml'] = $this->getRequest()->getParam('classHtml', '');
        $output['idHtml'] = $this->getRequest()->getParam('idHtml', '');
        
        $output['lang'] = Manager::getService('Session')->get('lang', 'fr');
        $this->_serviceTemplate = Manager::getService('FrontOfficeTemplates');
        $this->_servicePage = Manager::getService('PageContent');
        
        if ($this->getResponse() instanceof \Rubedo\Controller\Response) {
            
            $this->getHelper('Layout')->disableLayout();
            $this->getHelper('ViewRenderer')->setNoRender();
            $this->getResponse()->setBody($output, 'content');
            $this->getResponse()->setBody($template, 'template');
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
        } else {
            $content = $this->_serviceTemplate->render($template, $output);
            if (is_array($css)) {
                foreach ($css as $value) {
                    $this->view->headLink()->appendStylesheet($value);
                }
            }
            if (is_array($js)) {
                foreach ($js as $value) {
                    $this->view->headScript()->appendFile($value);
                }
            }
            
            $this->getHelper('ViewRenderer')->setNoRender();
            
            $this->getResponse()->appendBody($content, 'default');
        }
    }
}
