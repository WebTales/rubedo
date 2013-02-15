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

require_once ('AbstractController.php');
/**
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Blocks_ContentSingleController extends Blocks_AbstractController
{

    /**
     * Default Action, return the Ext/Js HTML loader
     */
    public function indexAction() {
        $this->_dataReader = Manager::getService('Contents');
        $this->_typeReader = Manager::getService('ContentTypes');

        $mongoId = $this->getRequest()->getParam('content-id');
        if (isset($mongoId) && $mongoId !=0) {
            $content = $this->_dataReader->findById($mongoId, true, false);
            $data = $content['fields'];
            if(isset($content['taxonomy'])){
    			$terms = array_pop($content['taxonomy']);
    			$termsArray = array();
    			foreach ($terms as $term) {
    				$termsArray[] = Manager::getService('TaxonomyTerms')->getTerm($term);
    			}
    			$data['terms']=$termsArray;
            }
            $data["id"] = $mongoId;

            $type = $this->_typeReader->findById($content['typeId'], true, false);
			$cTypeArray=array();
			foreach($type["fields"] as $value){
				
				$cTypeArray[$value['config']['name']]=$value["cType"];
			}
            $templateName = preg_replace('#[^a-zA-Z]#', '', $type["type"]);
            $templateName .= ".html.twig";
            $output["data"] = $data;
			$output["type"]=$cTypeArray;
			Manager::getService('PageContent')->setPageTitle($data['text']);
			
            $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/" . $templateName);
        }else{
        	$output= array();
        	 $template = Manager::getService('FrontOfficeTemplates')->getFileThemePath("blocks/single/noContent.html.twig");
        }
		
        $css = array();
        $js = array();

        $this->_sendResponse($output, $template, $css, $js);
    }
public function getContentsAction()
	{
		$this->_dataReader=Manager::getService('Contents');
		$returnArray=array();
		$data=$this->getRequest()->getParams();
		if(isset($data['block']['contentId']))
		{
		$content=$this->_dataReader->findById($data['block']['contentId']);
		$returnArray[]=array('text'=>$content['text'],'id'=>$content['id']);
		$returnArray['total']=count($returnArray);
		$returnArray["success"]=true;
		}else
			{
				$returnArray=array("success"=>false,"msg"=>"No query found");
			}
			$this->getHelper('Layout')->disableLayout();
            $this->getHelper('ViewRenderer')->setNoRender();
            $this->getResponse()->setBody(Zend_Json::encode($returnArray), 'data');
	}

}
