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
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_CarrouselController extends Blocks_ContentListController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction()
    {
        $this->_dataReader = Manager::getService('Contents');
        $isDraft = Zend_Registry::get('draft');
		/*get block config and data list*/
       	$blockConfig = $this->getRequest()->getParam('block-config');
		$contentArray=parent::getDataList($blockConfig,$this->setPaginationValues($blockConfig));      
		$data = array();
        foreach ($contentArray['data'] as $vignette) {
            $fields = $vignette['fields'];
			$terms = array_pop($vignette['taxonomy']);
			$termsArray = array();
			foreach ($terms as $term) {
				if($term=='50c0caeb9a199d1e11000001'){
					continue;
				}
				$termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
			}
			$fields['terms']=$termsArray;
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
            $data[] = $fields;
        }
        $output["items"] = $data;
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html.twig");
        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
