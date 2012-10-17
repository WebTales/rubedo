<?php 

/**
 * Action Helper for breadcrumb module
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperBreadCrumb extends Zend_Controller_Action_Helper_Abstract
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
     * Return array of links
     * 
     * @return array
     */	
    public function getBreadCrumb()
    {
    	$page = $this->getRequest()->getActionName();
 		$links= array(
		array('libelle'=>'Accueil','controller'=>'index','current'=>false),
		array('libelle'=> "$page",'controller'=>'#','current'=>true)
		);
		
        return($links);
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @return array
     */
    public function direct()
    {
        return $this->getBreadCrumb();
    }
}