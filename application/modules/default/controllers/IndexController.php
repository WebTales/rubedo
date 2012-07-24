<?php
use Rubedo\TestClass;


class IndexController extends Zend_Controller_Action
{

    public function init()
    {
    	$test = new TestClass();
    	var_dump($test);die();
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }


}

