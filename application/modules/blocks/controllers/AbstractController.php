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
/**
 *
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
    public function indexAction() {

        $this->_sendResponse($output, $template, $css, $js);
    }

    protected function _sendResponse($output, $template, array $css = null, array $js = null) {

        if ($this->getResponse() instanceof \Rubedo\Controller\Response) {
            $this->getHelper('Layout')->disableLayout();
            $this->getHelper('ViewRenderer')->setNoRender();
            $this->getResponse()->setBody($output, 'content');
            $this->getResponse()->setBody($template, 'template');
        } else {
            $this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
            $session = Rubedo\Services\Manager::getService('Session');
            $lang = $session->get('lang', 'fr');
            $this->_serviceTemplate->init($lang);
            $content = $this->_serviceTemplate->render($template, array('items' => $output));
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
