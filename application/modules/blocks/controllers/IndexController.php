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
class Blocks_IndexController extends Zend_Controller_Action
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {

        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();

        $session = Manager::getService('Session');
        $lang = $session->get('lang','fr');  
		
		$headerId = '507ff6a8add92a5809000000';
		$header = $this->getContentById($headerId);
		$output["title"] = $header['text'];
        $output["id"] = $headerId;
        $data = array();     

        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
		

		
        $filterArray = array('typeId' => '507fcc1cadd92af204000000');
        $this->_dataReader->addFilter($filterArray);
        $filterArray = array('status' => 'published');
        $this->_dataReader->addFilter($filterArray);

        $contentArray = $this->_dataReader->read();
        foreach ($contentArray as $vignette) {
            $fields = $vignette['fields'];
            $fields['title'] = $fields['text'];
            unset($fields['text']);
			$fields['id'] = (string) $vignette['id'];
            $data[] = $fields;
        }

        $output["data"] = $data;

        $beanObj = $this->getRequest()->getParam('beanObj');

        if (isset($beanObj)) {
            $beanObj->content = $output;
            $beanObj->template = "root/blocks/carrousel.html";
        } else {
            $this->_serviceTemplate = Rubedo\Services\Manager::getService('FrontOfficeTemplates');
            $session = Rubedo\Services\Manager::getService('Session');
            $lang = $session->get('lang', 'fr');
            $this->_serviceTemplate->init($lang);
            $content = $this->_serviceTemplate->render("root/blocks/carrousel.html", array('items'=>$output));

            $this->getResponse()->appendBody($content, 'default');
        }

        //var_dump($beanObj);	die();
        /*$this->getResponse()
         ->setBody(file_get_contents(APPLICATION_PATH . '/rubedo-backoffice-ui/www/app.html'));*/

    }

    /**
     * Get content by mongoId
     * @param int $contentId
     * @return array
     */
    public function getContentById($contentId) {
        $this->_dataReader = Manager::getService('MongoDataAccess');
        $this->_dataReader->init('Contents');
        $content = $this->_dataReader->findById($contentId);
        return $content;
    }

}
