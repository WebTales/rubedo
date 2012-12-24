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
class Blocks_CarrouselController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction()
    {
        $this->_dataReader = Manager::getService('Contents');

		//Zend_Debug::dump(Manager::getService('TaxonomyTerms')->getList());
		//die();
		//50c0c9ad9a199d3610000001
		
        
        $filterArray[] = array('property' => 'taxonomy.50c0cabc9a199dcc0f000002', 'value' => '50c0caeb9a199d1e11000001');
		//{"live.taxonomy.50c0cabc9a199dcc0f000002":"50c0caeb9a199d1e11000001"}
        //$filterArray[] = array('property' => 'online', 'value' => true);

        $isDraft = Zend_Registry::get('draft');
        
        $contentArray = $this->_dataReader->getOnlineList($filterArray);
		//($filters = null, $sort = null, $start = null, $limit = null, $live = true) 
		
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
		
		//Zend_debug::dump($data);
		//die();
		
        $output["items"] = $data;

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html.twig");

        $css = array();
        $js = array();
		
        $this->_sendResponse($output, $template, $css, $js);
    }

}
