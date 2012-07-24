<?php

use Rubedo\Mongo\DataAccess, Rubedo\Mongo;

class Backoffice_DataAccessController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $store = $this->getRequest()->getParam('store');


        
        $DataReader = new DataAccess($store);
        
        $dataStore = iterator_to_array($DataReader->find());
       
        
        $this->getHelper('Layout')->disableLayout();
        $this->getHelper('ViewRenderer')->setNoRender();
        $this->getResponse()->setHeader('Content-Type',
                "application/json",true);
        

        if(empty($dataStore)){
        
            $oldStore = file_get_contents(APPLICATION_PATH.'/rubedo-backoffice-ui/www/data/'.$store.'.json');
            $this->getResponse()->setBody($oldStore);
            /*$dataStore = json_decode(trim($oldStore),true);
            var_dump($dataStore);die();*/
        }else{
            $this->getResponse()->setBody(json_encode($dataStore));
        }
        

    }


}

