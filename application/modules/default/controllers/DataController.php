<?php

//require_once 'elastica/bootstrap.php';

class DataController extends AbstractController
{

    public function saveAction()
    {

		// get current language
		$defaultNamespace = new Zend_Session_Namespace('Default');
		$lang = $defaultNamespace->lang;
		
		// get data	
		$postId = explode("_", $this->_request->getPost('id'));
		$id = $postId[0];
		$field= $postId[1];
		
		$data = $this->_request->getPost('data');
		
		// load xml file
		$filename = 'data/'.$lang.'/'.$id.'.xml';
		$content = simplexml_load_file($filename); 
		//print_r($content);
		$fieldTag = $content->xpath(sprintf('//content[@id = "%d"]', $id));
		if ($fieldTag) {
			//$fieldTag[0]->$field = utf8_encode($data);
			$fieldTag[0]->$field = $data;
			// get content type
			print_r($fieldTag);
			$contentType = $fieldTag[0]['type'];
			$content->asXML($filename);
			$retour = array('success'=>$contentType);
		} else {
			$retour = array('failure'=>'content id not found');
			$this->getResponse()->setBody(Zend_Json::encode($retour));
			exit;
		}
		
		// push data into elastic search index
		try {
			// New Elastica instance
			$elasticaClient = new Elastica_Client();
	
			// Load index content
			$contentIndex = $elasticaClient->getIndex('content');	
			
			// Load content type 
			$contentType = $contentIndex->getType($contentType);
			
			// Build content document to index	
			$contentData = array();
			foreach($content->children() as $field => $var) {
				$contentData[$field] = (string) $var;
			}
			$contentData['lang'] = $lang;
			$contentData['type'] = (string) $fieldTag[0]['type'];

			$currentDocument = new Elastica_Document($lang.'_'.$id, $contentData);
			
			// Add content to type
			$contentType->addDocument($currentDocument);
	
			// Refresh Index
			$contentType->getIndex()->refresh();
			//$retour = array('success'=>$filename);
		} catch  (Exception $e) {
		    $retour =  array('echec'=>$e->getMessage()."\n");
		}
		// send result

		$this->getResponse()->setBody(Zend_Json::encode($retour));
		
    }
	
	
    public function getAction($id,$lang,$var)
    {
    	if (is_array($var)) {
    		$result = array();
    		foreach($var as $v) $result[$v] = @file_get_contents('data/'.$lang.'/'.$id.'_'.$v.'.html');
			$result["id"]=$id;
    	} else {
    		$result = @file_get_contents('data/'.$lang.'/'.$id.'_'.$var.'.html');
		}
		return $result;
	}
	
    public function getXMLAction($id,$lang)
    {
		
    	$content = simplexml_load_file(APPLICATION_PATH.'/../data/static/'.$lang.'/'.$id.'.xml'); 

    	$result = array();
		
		// get content attributes
		foreach($content->attributes() as $field => $var) {
			$result[$field]=$var;
		}		

		// get content fields
		foreach($content->children() as $field => $var) {
			$result[$field]=$var;
		}				
				
		return $result;

	}
	
}

