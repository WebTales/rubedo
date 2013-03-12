<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2013, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
use Rubedo\Services\Manager;

/**
 * Installer Controller
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Install_DataTestController extends Zend_Controller_Action
{

    public function indexAction ()
    {
        if ($this->getParam('doCreateGroups', false)) {
            $this->view->isCreated = $this->_doCreateGroups();
        }
    }

    protected function _doCreateGroups ()
    {
        $success = $this->_docreateGroup('backoffice');
        $rolesArray = Manager::getService('Acl')->getAvailaibleRoles();
        
        foreach ($rolesArray['data'] as $role){
            if(in_array($role['id'], array('admin','public','backoffice'))){
                continue;
            }
            $success = $success && $this->_docreateGroup($role['id']);
        }
        return $success;
    }
    
    protected function _docreateGroup($roleId,$workspace = "global"){
        $name = ($workspace =='global')?$roleId:$roleId.'_'.$workspace;
        $newGroup = array();
        $newGroup['canDeleteElements']=1;
        $newGroup['canWriteUnownedElements']=1;
        $newGroup['expandable']=!isset($this->_backofficeId);
        $newGroup['members']= array();
        $newGroup['name']=$name;
        $newGroup["readWorkspaces"]=array($workspace);
        $newGroup["roles"]=array($roleId);
        $newGroup['writeWorkspaces']=$newGroup["readWorkspaces"];
        $newGroup["defaultWorkspace"]=$workspace;
        $newGroup['parentId']=isset($this->_backofficeId)?$this->_backofficeId:'root';
        
        $user = array();
        $user['login']=$name;
        $user['name']=$name;
        $user['salt'] = Manager::getService('Hash')->generateRandomString();
        $user['password'] = Manager::getService('Hash')->derivatePassword('coincoin', $user['salt']);
        $user['email'] = $name.'@webtales.fr';
        
        $response = Manager::getService('Users')->create($user);
        $result = $response['success'];
        if($result){
            $userId = $response['data']['id'];
            $newGroup['members']= array($userId);
            $responseGroup = Manager::getService('Groups')->create($newGroup);
            $resultGroup = $responseGroup['success'];
            if($roleId =="backoffice"){
                $this->_backofficeId = $responseGroup['data']['id'];
            }
            return $responseGroup;
        }else{
            return false;
        }
        
        
    }

    protected function _doInsertContents ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/../data/default/';
        $contentIterator = new DirectoryIterator($contentPath);
        foreach ($contentIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            if (in_array($directory->getFilename(), array(
                'groups',
                'site'
            ))) {
                continue;
            }
            $collection = ucfirst($directory->getFilename());
            $itemsJson = new DirectoryIterator($contentPath . '/' . $directory->getFilename());
            foreach ($itemsJson as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }
                if ($file->getExtension() == 'json') {
                    $itemJson = file_get_contents($file->getPathname());
                    $item = Zend_Json::decode($itemJson);
                    $result = Manager::getService($collection)->create($item);
                    $success = $result['success'] && $success;
                }
            }
        }
        
        if (! $success) {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'failed to initialize contents';
        } else {
            $this->view->isContentInitialized = true;
        }
        
        return $success;
    }
}

