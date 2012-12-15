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
class Blocks_SimpleContentController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $this->_dataReader = Manager::getService('Contents');

       	$mongoId = $this->getRequest()->getParam('content-id','507fd4feadd92aa602000000');
        $content = $this->_dataReader->findById($mongoId);
        $data = $content['fields'];
        $data["id"] = $mongoId;
		$data['title'] = $data['text'];
        $output = $data;
		
        $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/simplecontent.html.twig");

        $css = array();
        $js = array();
        $this->_sendResponse($output, $template, $css, $js);
    }

}
