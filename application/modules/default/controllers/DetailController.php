<?php

require_once '/elastica/bootstrap.php';

class DetailController extends AbstractController
{

	public $page;
	public $template;
	public $blocks = array();
	
    public function init()
    {

		$this->blocks = array(
			array('Module'=>'NavBar','Input'=>null,'Output'=>'navbar_content'),
			array('Module'=>'BreadCrumb','Input'=>null,'Output'=>'liens'),
			array('Module'=>'PopIn','Input'=>1,'Output'=>'popin_about'),
			array('Module'=>'PopIn','Input'=>2,'Output'=>'popin_connect'),
			array('Module'=>'PopIn','Input'=>3,'Output'=>'popin_confirm')
		);
		
		$twigVar = array();
		foreach($this->blocks as $block) {
			$helper= 'helper'.$block['Module'];
			$output = $block['Output'];
			$input = $block['Input'];
			$twigVar[$output] = $this->_helper->$helper($input);
		}
		
		$id = $this->_getParam('id');
		
		$session = Manager::getService('Session');
        $lang = $session->get('lang','fr');
		
		if (file_exists('data/'.$lang.'/'.$id.'.xml')) {
			$twigVar['content'] =  DataController::getXMLAction($id,$lang);
		} else {
			$this->_redirector = $this->_helper->getHelper('Redirector');
       		$this->_redirector->gotoUrl('/');
		}
		
        $this->twig('detail.html',$twigVar);

    }
		
    public function indexAction()
    {

    }
	
}