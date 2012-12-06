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

        $headerId = '507ff6a8add92a5809000000';
        $header = $this->_dataReader->findById($headerId);
        $output["title"] = $header['text'];
        $output["id"] = $headerId;
        $data = array();

        $filterArray[] = array('property' => 'typeId', 'value' => '507fcc1cadd92af204000000');
        $filterArray[] = array('property' => 'status', 'value' => 'published');

        $contentArray = $this->_dataReader->getList($filterArray);
        foreach ($contentArray['data'] as $vignette) {
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
            $fields['id'] = (string)$vignette['id'];
            $data[] = $fields;
        }

        $output["items"] = $data;

        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/carrousel.html");

        $css = array();
        $js = array();
		
        $this->_sendResponse($output, $template, $css, $js);
    }

}
