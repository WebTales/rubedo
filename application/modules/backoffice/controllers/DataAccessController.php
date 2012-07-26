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
 * Controller dealing with the data access
 * 
 * Receveive Ajax Calls for read & write from the UI to the Mongo DB
 * 
 * 
 * @author jbourdin
 *
 */
class Backoffice_DataAccessController extends Zend_Controller_Action
{



    /**
     * The default read Action
     * 
     * Return the content of the collection base on the "store" parameter
     */
    public function indexAction ()
    {
        $store = $this->getRequest()
            ->getParam('store');
        
        $dataReader = new DataAccess($store);
        
        $dataStore = iterator_to_array($dataReader->find());
        
        if (empty($dataStore)) {
            
            $oldStore = file_get_contents(APPLICATION_PATH . '/rubedo-backoffice-ui/www/data/' . $store . '.json');
            $this->getResponse()
                ->setBody($oldStore);
            // $dataStore = Zend_Json::decode($oldStore);
        } else {
            $this->getResponse()
                ->setBody(json_encode($dataStore));
        }
        $this->getHelper('Layout')
            ->disableLayout();
        $this->getHelper('ViewRenderer')
            ->setNoRender();
        $this->getResponse()
            ->setHeader('Content-Type', "application/json", true);
    }
}

