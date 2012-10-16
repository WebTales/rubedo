<?php 

require_once '../application/modules/default/controllers/DataController.php';

/**
 * Action Helper for headline module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperHeadLine extends Zend_Controller_Action_Helper_Abstract
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
     * Return array describing a headline
     * 
     * @return array
     */	
    public function getHeadLine()
    {
    	
    	$id = 99; // block_id

		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		
		$output = DataController::getXMLAction($id,$lang);
		$output["id"] = $id;
		
        return $output;

    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getHeadLine();
    }
}