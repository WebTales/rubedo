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

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ContentSingleController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $this->_dataReader = Manager::getService('Contents');
		$this->_typeReader = Manager::getService('ContentTypes');
		
       	$mongoId = $this->getRequest()->getParam('content-id');
        $content = $this->_dataReader->findById($mongoId,true,false);
        $data = $content['fields'];
        $data["id"] = $mongoId;

		$type=$this->_typeReader->findById($content['typeId'],true,false);
		$templateName=preg_replace('#[^a-zA-Z]#', '', $type["type"]);
		$templateName .= ".html";
        $output["data"] = $data;
		$template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/".$templateName);

        $css = array('/css/rubedo.css', '/css/bootstrap-responsive.css');
        $js = array("/js/jquery.js", "/js/bootstrap-transition.js", "/js/bootstrap-alert.js", "/js/bootstrap-modal.js", "/js/bootstrap-dropdown.js", "/js/bootstrap-scrollspy.js", "/js/bootstrap-tab.js", "/js/bootstrap-tooltip.js", "/js/bootstrap-popover.js", "/js/bootstrap-button.js", "/js/bootstrap-collapse.js", "/js/bootstrap-carousel.js", "/js/bootstrap-typeahead.js", );
		
        $this->_sendResponse($output, $template, $css, $js);
    }

}
