<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */

require_once('DataAccessController.php');
 
/**
 * Controller providing CRUD API for the sitesController JSON
 *
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class Backoffice_SitesController extends Backoffice_DataAccessController
{
    public function init(){
		parent::init();
		
		// init the data access service
		$this -> _dataService = Rubedo\Services\Manager::getService('Sites');
	}
	
	public function wizardCreateAction()
	{
	 $data = $this->getRequest()->getParam('data');

        if (!is_null($data)) {
            $insertData = Zend_Json::decode($data);
            if (is_array($insertData)) {
                $site= $this->_dataService->create($insertData, true);
            }}
		if($site['success']===true)
		{
			$firstColumnId=(string) new MongoId();
			$secondColumnId=(string) new MongoId();
			$maskObj=array("site"=>$site['data']['id'],'text'=>"Default-Mask",
			"rows"=>array(
			0=>array(
				"mType"=>"row",
				"id"=>(string) new MongoId(),
				"eTitle"=>"title",
				"classHTML"=>null,
				"idHTML"=>null,
				"height"=>null,
				"displayTitle"=>null,
				"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
				"columns"=>array(0=>array(
					"mType"=>"col",
					"id"=>$firstColumnId,
					"eTitle"=>"title",
					"classHTML"=>null,
					"idHTML"=>null,
					"displayTitle"=>null,
					"span"=>12,
					"offset"=>0,
					"rows"=>null,
					"isTerminal"=>true,
					"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
					))
				),
			1=>array(
				"mType"=>"row",
				"id"=>(string) new MongoId(),
				"eTitle"=>"title",
				"classHTML"=>null,
				"idHTML"=>null,
				"height"=>null,
				"displayTitle"=>null,
				"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
				"columns"=>array(0=>array(
					"mType"=>"col",
					"id"=>$secondColumnId,
					"eTitle"=>"title",
					"classHTML"=>null,
					"idHTML"=>null,
					"displayTitle"=>null,
					"span"=>12,
					"offset"=>0,
					"rows"=>null,
					"isTerminal"=>true,
					"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
						))
					)
				),
				"blocks"=>array(0=>array(
					"bType" =>"Bloc de navigation" ,
					"title"=>"Bloc de navigation",
					"id" =>(string) new MongoId(), 
					"parentCol" =>$firstColumnId,
					"canEdit"=>null,
					"mType" =>"block" ,
					"classHTML"=>null,
					"displayTitle"=>null,
					"urlPrefix"=>null,
					"flex"=>1,
					"idHTML"=>null,
					"orderValue"=>100,
					"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
					"configBloc"=>array(),
					"champsConfig"=>array(
						"avance"=>array(),
						"simple"=>array(
						0=>array(
							"categorie"=>"Pages",
							"champs"=>array(
								0=>array(
									"config"=>array(
										"fieldLabel"=>"Racine",
										"name"=>"rootPage"
									),
									"type"=>"Ext.ux.TreePicker",
								),
								1=>array(
									"config"=>array(
										"fieldLabel"=>"Page de recherche",
										"name"=>"searchPage"
									),
									"type"=>"Ext.ux.TreePicker",
								),
								2=>array(
									"config"=>array(
										"fieldLabel"=>"Moteur de recherche",
										"name"=>"useSearchEngine"
										),
										"type"=>"Ext.form.field.Checkbox",
								),
								)
							)
						)
					)
				)
			)
			);
			$mask=Rubedo\Services\Manager::getService('Masks')->create($maskObj,true);
			if($mask['success']===true)
			{
				/*Create Home Page*/
				$homePageObj=array("site"=>$site['data']['id'],'title'=>"accueil","maskId"=>$mask['data']['id'],"parentId"=>'root',"description"=>"","keywords"=>array(),"blocks"=>array());
				$homePage=Rubedo\Services\Manager::getService('Pages')->create($homePageObj,true);
				/*Create Single Page*/
				$pageObj=array("site"=>$site['data']['id'],'title'=>"single","maskId"=>$mask['data']['id'],"excludeFromMenu"=>true,"parentId"=>'root',"description"=>"","keywords"=>array(),
				"blocks"=>array(
				0=>array(
					"bType"=>"Détail de contenu",
					"canEdit"=>true,
					"classHTML"=>null,
					"displayTitle"=>null,
					"flex"=>1,
					"id"=>(string) new MongoId(),
					"idHTML"=>null,
					"mType"=>"block",
					"orderValue"=>1,
					"parentCol"=>$secondColumnId,
					"title"=>"Détail de contenu",
					"urlPrefix"=>null,
					"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
					"configBloc"=>array("recievesParam"=>true),
					"champsConfig"=>array(
						"avance"=>array(),
						"simple"=>array(
							0=>array(
								"categorie"=>"Affichage",
								"champs"=>array(
									0=>array(
										"config"=>array(
											"fieldLabel"=>"Type d'affichage",
											"name"=>"displayType"
										),
										"type"=>"Ext.form.field.Text"
									)
								)
							),
							1=>array(
								"categorie"=>"Contenu",
								"champs"=>array(
									0=>array(
										"config"=>array(
											"fieldLabel"=>"Contenu à afficher",
											"name"=>"contentId"
										),
										"type"=>"Rubedo.view.FCCField"
									),
									1=>array(
										"config"=>array(
											"fieldLabel"=>"Paramètre externe",
											"name"=>"recievesParam"
										),
										"type"=>"Ext.form.field.Checkbox"
									)
								)
							)
						)
					)
				)
				));
				$page=Rubedo\Services\Manager::getService('Pages')->create($pageObj,true);
				/*Create Search Page*/
				$searchPageObj=array("site"=>$site['data']['id'],'title'=>"search","excludeFromMenu"=>true,"maskId"=>$mask['data']['id'],"parentId"=>'root',"description"=>"","keywords"=>array(),
				"blocks"=>array(
				0=>array(
				"bType"=>"Résultat de recherche",
				"canEdit"=>true,
				"classHTML"=>null,
				"displayTitle"=> null,
				"flex"=> 1,
				"id"=>(string) new MongoId(),
				"idHTML"=> null,
				"mType"=> "block",
				"orderValue"=> 1,
				"responsive"=>array("phone"=>true,"tablet"=>true,"desktop"=>true),
				"parentCol"=>$secondColumnId,
				"title"=>"Résultat de recherche",
				"urlPrefix"=>null,
				"configBloc"=>array("constrainToSite"=>true),
				"champsConfig"=>array(
					"avance"=>array(),
					"simple"=>array(
						0=>array(
							"categorie"=>"Filtrage",
							"champs"=>array(
								0=>array(
									"config"=>array(
										"fieldLabel"=>"Restreindre au site",
										"name"=>"constrainToSite",
									),
									"type"=>"Ext.form.field.Checkbox"
								),
								1=>array(
									"config"=>array(
										"fieldLabel"=>"Filtres",
										"name"=>"filters",

									),
									"type"=>"Ext.form.field.TextArea"
								)
							)
						)
					
					)
				)
				)		
				));
				$searchPage=Rubedo\Services\Manager::getService('Pages')->create($searchPageObj,true);
				if($page['success']===true)
				{
					$updateMask=$mask['data'];
					$updateMask["blocks"][0]['configBloc']=array("useSearchEngine"=>true,"rootPage"=>$homePage['data']['id'],"searchPage"=>$searchPage['data']['id']);
					Rubedo\Services\Manager::getService('Masks')->update($updateMask, true);
					
					$updateData=$site['data'];
					$updateData['homePage']=$homePage['data']['id'];
					$returnArray=$this->_dataService->update($updateData, true);
				}
			}
		} if (!$returnArray['success']) {
            $this->getResponse()->setHttpResponseCode(500);
        }
        $this->_returnJson($returnArray);
	}
	
}