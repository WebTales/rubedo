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
        //$mongo = new \Mongo("mongodb://localhost");
    	$test = new DataAccess('things');
    	/*$obj = array( "title" => "Calvin and Hobbes", "author" => "Bill Watterson" );
    	Zend_Debug::dump($test->insert($obj));*/
    	Zend_Debug::dump(iterator_to_array($test->find(array(),array('title','author'))));
    	die();
    }


}

