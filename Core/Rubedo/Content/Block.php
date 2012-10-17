<?php

/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

namespace Rubedo\Content;

Use Rubedo\Interfaces\Content\IBlock;
/**
 * Block Content Service
 *
 * Get current user and user informations
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Block implements IBlock
{
    /**
     * Return the data associated to a block given by config array
     * @param array $blockConfig
     * @param Zend_Controller_Action $parentController
     * @return array
     */
    public function getBlockData($blockConfig, $parentController) {
        $helper = 'helper' . $blockConfig['Module'];
        $output = $blockConfig['Output'];
        $input = $blockConfig['Input'];
        switch($blockConfig['Module']) {
			case 'Carrousel':
				$content = $this->getCarrousel();
				break;
            default :
                $content = $parentController->getProtectedHelper()->$helper($input);
                break;
        }

        return array($output => $content);
    }
	
	/**
     * Return carousel content
     * 
     * @return array
     */	
    protected function getCarrousel()
    {
       	// get data
    	
		$id = array("201","202","203","204","205");

		$defaultNamespace = new \Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		$title = \DataController::getXMLAction("200",$lang);
    	$output["title"] = $title['title'];
		$output["id"] = "200";
		$data = array();
		for ($i=0;$i<=4;$i++) $data[] = \DataController::getXMLAction($id[$i],$lang);
		$output["data"] =  $data;

        return $output;
    }

}
