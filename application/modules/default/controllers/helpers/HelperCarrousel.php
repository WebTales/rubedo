<?php 

/**
 * Action Helper for carrousel module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperCarrousel extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_Loader_PluginLoader
     */
    public $pluginLoader;

    /**
     * Constructor: initialize plugin loader
     * 
     * @return void
     */
    public function __construct()
    {
        $this->pluginLoader = new Zend_Loader_PluginLoader();
    }

    /**
     * Return carousel content
     * 
     * @return array
     */	
    public function getCarrousel()
    {
       	// get data
    	
		$id = array("201","202","203","204","205");

		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		$title = DataController::getXMLAction("200",$lang);
    	$output["title"] = $title['title'];
		$output["id"] = "200";
		$data = array();
		for ($i=0;$i<=4;$i++) $data[] = DataController::getXMLAction($id[$i],$lang);
		$output["data"] =  $data;

        return $output;
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getCarrousel();
    }
}