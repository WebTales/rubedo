<?php

class IndexControllerTest extends AbstractControllerTest
{
	
	public function testExist(){
		$this->dispatch('/');
	}

	/**
	 * check the service configuration by getservice method
	 */
	public function testServiceCurrentUser(){
		$currentUserService = \Rubedo\Services\Manager::getService('CurrentUser');
    	//$currentUser = $currentUserService->getCurrentUserSummary();
	}

}

