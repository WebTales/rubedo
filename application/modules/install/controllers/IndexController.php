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
use Rubedo\Mongo\DataAccess, Rubedo\Services\Manager;

/**
 * Installer Controller
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Install_IndexController extends Zend_Controller_Action
{

    protected $_localConfigDir;

    protected $_localConfigFile;

    protected $_localConfig;

    public function init ()
    {
        $this->_helper->_layout->setLayout('install-layout');
        
        $this->_localConfigDir = realpath(APPLICATION_PATH . '/configs/local/');
        $this->_localConfigFile = $this->_localConfigDir . '/config.json';
        $this->_loadLocalConfig();
        $this->_applicationOptions = $this->getFrontController()
            ->getParam('bootstrap')
            ->getApplication()
            ->getOptions();
        $this->view->options = $this->_applicationOptions;
    }

    public function indexAction ()
    {
        if (! $this->_isConfigWritable()) {
            throw new Rubedo\Exceptions\User(
                    'Local config file ' . $this->_localConfigFile .
                             ' should be writable');
        }
        if (! isset($this->_localConfig['installed']) ||
                 $this->_localConfig['installed']['status'] != 'finished') {
            if (! isset($this->_localConfig['installed']['action'])) {
                $this->_localConfig['installed']['action'] = 'start-wizard';
            }
            $action = $this->_localConfig['installed']['action'];
            $this->_forward($action);
        }
    }

    public function startWizardAction ()
    {
        $this->_localConfig['installed'] = array(
                'status' => 'begin',
                'action' => 'start-wizard'
        );
        
        $this->_saveLocalConfig();
    }

    /**
     * Check if a valid connection to MongoDB can be written in local config
     */
    public function setDbAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-db';
        }
        
        $mongoOptions = isset($this->_applicationOptions["datastream"]["mongo"]) ? $this->_applicationOptions["datastream"]["mongo"] : array();
        
        $dbForm = Install_Model_DbConfigForm::getForm($mongoOptions);
        
        $mongoAccess = new DataAccess();
        
        try {
            if ($this->getRequest()->isPost() &&
                     $dbForm->isValid($this->getAllParams())) {
                $params = $dbForm->getValues();
                $mongo = $this->_buildConnectionString($params);
                $dbName = $params['db'];
                $initCollection = $mongoAccess->init('Users', $dbName, $mongo);
            } else {
                $initCollection = $mongoAccess->init('Users');
                $params = $this->_applicationOptions["datastream"]["mongo"];
            }
            $connectionValid = true;
        } catch (Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->view->isReady = true;
            $this->_localConfig["datastream"]["mongo"] = $params;
        } else {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'Rubedo can\'t connect itself to specified DB';
        }
        
        $this->view->form = $dbForm;
        
        $this->_saveLocalConfig();
    }

    public function setDbContentsAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-db-contents';
        }
        
        if($this->getParam('doEnsureIndex',false)){
            
        }
        
        if($this->getParam('doInsertGroups',false)){
        
        }
        
        $this->view->isReady = true;
        
        $this->_saveLocalConfig();
    }

    public function setAdminAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-admin';
        }
        
        $form = Install_Model_AdminConfigForm::getForm();
        
        if ($this->getRequest()->isPost() &&
                 $form->isValid($this->getAllParams())) {
            $params = $form->getValues();
            $hashService = \Rubedo\Services\Manager::getService('Hash');
            
            $params['salt'] = $hashService->generateRandomString();
            $params['password'] = $hashService->derivatePassword(
                    $params['password'], $params['salt']);
            
            $userService = Manager::getService('MongoDataAccess');
            $userService->init('Users');
            $response = $userService->create($params);
            $result = $response['success'];
            
            if (! $result) {
                $this->view->hasError = true;
                $this->view->errorMsg = $response['msg'];
            } else {
                $userId = $response['data']['id'];
                
                $groupService = Manager::getService('MongoDataAccess');
                $groupService->init('Groups');
                $adminGroup = $groupService->findOne(array(
                        'name' => 'admin'
                ));
                $adminGroup['members'][] = $userId;
                $groupService->update($adminGroup);
                $this->view->accountName = $params['name'];
            }
            
            $this->view->creationDone = $result;
        }
        
        $listAdminUsers = Manager::getService('Users')->getAdminUsers();
        
        if ($listAdminUsers['count'] > 0) {
            $this->view->hasAdmin = true;
            $this->view->adminAccounts = $listAdminUsers['data'];
            $this->view->isReady = true;
        } else {
            
            $this->view->errorMsgs = 'No Admin Account Set';
        }
        
        $this->view->form = $form;
        
        $this->_saveLocalConfig();
    }

    protected function _buildConnectionString ($options)
    {
        $connectionString = 'mongodb://';
        if (! empty($options['login'])) {
            $connectionString .= $options['login'];
            $connectionString .= ':' . $options['password'] . '@';
        }
        $connectionString .= $options['server'];
        return $connectionString;
    }

    protected function _isConfigWritable ()
    {
        $isWritable = false;
        if (is_file($this->_localConfigFile)) {
            return is_writable($this->_localConfigFile);
        } else {
            return is_writable($this->_localConfigDir);
        }
    }

    protected function _saveLocalConfig ()
    {
        $iniWriter = new Zend_Config_Writer_Json();
        $iniWriter->setConfig(new Zend_Config($this->_localConfig));
        $iniWriter->setFilename($this->_localConfigFile);
        $iniWriter->setPrettyPrint(true);
        $iniWriter->write();
    }

    protected function _loadLocalConfig ()
    {
        if (is_file($this->_localConfigFile)) {
            $localConfig = new Zend_Config_Json($this->_localConfigFile, null, 
                    array(
                            'allowModifications' => true
                    ));
        } elseif (is_file(APPLICATION_PATH . '/configs/local.ini')) {
            $localConfig = new Zend_Config_Ini(
                    APPLICATION_PATH . '/configs/local.ini', null, 
                    array(
                            'allowModifications' => true
                    ));
        } else {
            $localConfig = new Zend_Config(array(), true);
        }
        $this->_localConfig = $localConfig->toArray();
    }
}

