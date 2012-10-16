<?php 

require_once '../application/modules/default/controllers/DataController.php';

/**
 * Action Helper for content list module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperContentList extends Zend_Controller_Action_Helper_Abstract
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
     * Return array of images
     * 
     * @return array
     */	
    public function getContentList()
    {
    	// get data
    	$output = array();
		$id = array("111","112","113","114","115","116","117","118");

		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		
		for ($i=0;$i<=7;$i++) $output[] = DataController::getXMLAction($id[$i],$lang);

        return $output;
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getContentList();
    }
}