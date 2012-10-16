<?php 

/**
 * Action Helper for simple content module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperSimpleContent extends Zend_Controller_Action_Helper_Abstract
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
     * Return array describing a simple content
     * 
	 * @param block_id block identifier
     * @return array
     */	
    public function getSimpleContent($block_id)
    {

		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		
		$output = DataController::getXMLAction($block_id,$lang);
		$output["id"] = $block_id;
		
        return $output;

    }

    /**
     * Strategy pattern: call helper as broker method
     * 
	 * @param block_id block identifier
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getSimpleContent($block_id);
    }
}