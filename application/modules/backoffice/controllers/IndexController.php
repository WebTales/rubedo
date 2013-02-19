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
use Rubedo\Mongo\DataAccess, Rubedo\Mongo;

/**
 * Back Office Defautl Controller
 * 
 * Invoked when calling /backoffice URL
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Backoffice_IndexController extends Zend_Controller_Action
{



    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction ()
    {
		$this->_auth = Rubedo\Services\Manager::getService('Authentication');
		
		if(!$this->_auth->getIdentity()){
			$this->_helper->redirector->gotoUrl("/backoffice/login");
		}
		
		$appHtml = file_get_contents(APPLICATION_PATH . '/../public/components/webtales/rubedo-backoffice-ui/www/app.html');
        
		$extjsNativeInclude = '<script src="extjs-4.1.0/ext-all-debug.js"></script>';
		$rubedoFavIcone='<link rel="shortcut icon" type="image/x-icon" href="/backoffice/resources/icones/faviconRubedo.ico"/>';
		$extjsOptions = Zend_Registry::get('extjs');
		
		if(!isset($extjsOptions['debug']) ||$extjsOptions['debug']==false){
		    $extjsInclude = '<script src="extjs-4.1.0/ext-all.js"></script>'.$rubedoFavIcone;
		    //http://cdn.sencha.com/ext-4.1.0-gpl/
		    
		}else{
		    $extjsInclude = $extjsNativeInclude.$rubedoFavIcone;
		}
		$appHtml = str_replace($extjsNativeInclude, $extjsInclude, $appHtml);
		
		if(isset($extjsOptions['network']) && $extjsOptions['network']=='cdn'){
		    $appHtml = str_replace('extjs-4.1.0/', 'http://cdn.sencha.com/ext-4.1.0-gpl/', $appHtml);
		}else{
		    $appHtml = str_replace('extjs-4.1.0/', $this->view->baseUrl().'/components/sensha/extjs/', $appHtml);
		}
		
		
		
        $this->getHelper('Layout')
            ->disableLayout();
        $this->getHelper('ViewRenderer')
            ->setNoRender();
        $this->getResponse()
            ->setBody($appHtml);
    }
}

