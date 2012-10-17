<?php 

/**
 * Action Helper for an IFrame
 * 
 * @uses Zend_Controller_Action_Helper_Abstract
 */
class Controller_Helper_HelperIFrame extends Zend_Controller_Action_Helper_Abstract
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
     * Return array describing an iframe content
     * 
     * @return array
     */	
    public function getIFrame()
    {
    	$id = 98324;
		$fr = array(
    		'title' => 'Plan d\'accès',
    		'width' => 526,
    		'height' => 366,
    		'frameborder' => 0,
    		'scrolling' => 'no',
    		'marginheight' => 0,
    		'marginwidth' => 0,
			'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=fr&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed',
		);
		$en = array(
    		'title' => 'Area map',
			'width' => 526,
    		'height' => 366,
    		'frameborder' => 0,
    		'scrolling' => 'no',
    		'marginheight' => 0,
    		'marginwidth' => 0,
			'src' => 'http://maps.google.fr/maps?georestrict=input_srcid:d6c0e9367f692930&hl=en&ie=UTF8&view=map&cid=4303835548001045871&q=Incubateur+Centrale+Paris&ved=0CBkQpQY&ei=gILZTMuBH8Xujgf2ypj_CA&hq=Incubateur+Centrale+Paris&hnear=&iwloc=A&sll=46.75984,1.738281&sspn=11.232446,19.753418&output=embed',
		);
		
		$defaultNamespace = new Zend_Session_Namespace('Default');
		if (!isset($defaultNamespace->lang)) $defaultNamespace->lang="fr";
		$lang = $defaultNamespace->lang;
		
		$output = $$lang;
		$output['id'] = $id;

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
        return $this->getIFrame();
    }
}