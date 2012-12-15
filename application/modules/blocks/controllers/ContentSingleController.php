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
        if (isset($mongoId) && $mongoId !=0) {
            $content = $this->_dataReader->findById($mongoId, true, false);
            $data = $content['fields'];
			$terms = array_pop($content['taxonomy']);
			$termsArray = array();
			foreach ($terms as $term) {
				$termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
			}
			$data['terms']=$termsArray;
            $data["id"] = $mongoId;

            $type = $this->_typeReader->findById($content['typeId'], true, false);
            $templateName = preg_replace('#[^a-zA-Z]#', '', $type["type"]);
            $templateName .= ".html.twig";
            $output["data"] = $data;
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/" . $templateName);
        }else{
        	$output= array();
        	 $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/noContent.html.twig");
        }
		
        $css = array();
        $js = array();

        $this->_sendResponse($output, $template, $css, $js);
    }

}
