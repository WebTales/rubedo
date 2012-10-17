<?php 

/**
 * Action Helper for navigation bar
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperNavBar extends Zend_Controller_Action_Helper_Abstract
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
     * Return navbar content
     * 
     * @return array
     */	
    public function getNavBar()
    {
    	// images examples
    	// TODO : load data from services
    	$id = "987194"; // block id
		$responsive = true; // responsive : true or false
		$position = "static-top"; // position : none, fixed-top, fixed-bottom, static-top
		$brand = "Rubedo"; // brand
		$options = array("loginform","langselector","themechooser","search");
    	$fr = array(
    		array('id' => 1,'type' => 'link','caption' => 'A propos','href' => '#about','colapse' => true,'modal' => true,'icon' => 'icon-info-sign'),
    		array('id' => 2,'type' => 'link','caption' => 'Contact','href' => '/index/contact','colapse' => true,'modal' => false, 'icon' => 'icon-envelope'),
    		array('id' => 3,'type' => 'dropdown','caption' => 'Rubedo à la loupe','colapse' => true,'modal' => false, 'icon' => 'icon-zoom-in',
					'list' => array(
					array('caption' => 'Mobilité', 'href' => '/index/responsive'),
					array('caption' => 'Accessibilité', 'href' => '/index/accessible'),
					array('caption' => 'Performances', 'href' => '/index/performant'),
					array('caption' => 'Ergonomie', 'href' => '/index/ergonomic'),
					array('caption' => 'Richesse', 'href' => '/index/rich'),
					array('caption' => 'Extensibilité', 'href' => '/index/extensible'),
					array('caption' => 'Robustesse', 'href' => '/index/solid'),
					array('caption' => 'Pérénité', 'href' => '/index/durable')
					)
				)
    	);	
    	$en = array(
    		array('id' => 1,'type' => 'link','caption' => 'About','href' => '#about', 'colapse' => true,'modal' => true,'icon' => 'icon-info-sign'),
    		array('id' => 2,'type' => 'link','caption' => 'Contact','href' => '/index/contact', 'colapse' => true, 'modal' => false, 'icon' => 'icon-envelope'),
    		array('id' => 3,'type' => 'dropdown','caption' => 'Close-up on Rubedo','colapse' => true, 'modal' => false, 'icon' => 'icon-zoom-in',
					'list' => array(
					array('caption' => 'Mobile', 'href' => '/index/responsive'),
					array('caption' => 'Accessible', 'href' => '/index/accessible'),
					array('caption' => 'Performant', 'href' => '/index/performant'),
					array('caption' => 'Ergonomic', 'href' => '/index/ergonomic'),
					array('caption' => 'Rich', 'href' => '/index/rich'),
					array('caption' => 'Extensible', 'href' => '/index/extensible'),
					array('caption' => 'Solid', 'href' => '/index/solid'),
					array('caption' => 'Durable', 'href' => '/index/durable')
					)
				)
    	);
 
		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;

    	$output["id"] = $id;
		$output["responsive"] = $responsive;
		$output["position"] = $position;
    	$output["brand"] = $brand;
		$output["options"] = $options;
		$output["components"] =  $$lang;

        return $output;
    }

    /**
     * Strategy pattern: call helper as broker method
     * 
     * @return array
     */
    public function direct($block_id)
    {
        return $this->getNavBar();
    }
}