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
use Rubedo\Mongo\DataAccess, Rubedo\Collection\AbstractCollection, Rubedo\Services\Manager, WebTales\MongoFilters\Filter;

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

    protected $_applicationOptions = array();

    public function init ()
    {
        Rubedo\User\CurrentUser::setIsInstallerUser(true);
        
        $wasFiltered = AbstractCollection::disableUserFilter();
        $this->_helper->_layout->setLayout('install-layout');
        
        $this->_navigation = Install_Model_NavObject::getNav();
        $this->view->navigationContainer = $this->_navigation;
        $this->_setWizardSteps();
        
        $this->_localConfigDir = realpath(APPLICATION_PATH . '/configs/local/');
        $this->_localConfigFile = $this->_localConfigDir . '/config.json';
        $this->view->moduleDir = realpath(__DIR__ . '/..');
        $this->view->localConfigFile = $this->_localConfigFile;
        $this->_loadLocalConfig();
        $this->_applicationOptions = $this->getFrontController()
            ->getParam('bootstrap')
            ->getApplication()
            ->getOptions();
        $this->view->options = $this->_applicationOptions;
    }

    /**
     * get previous and next step for wizard
     */
    protected function _setWizardSteps ()
    {
        $getNext = false;
        $previous = null;
        foreach ($this->_navigation as $page) {
            if ($getNext) {
                $this->view->next = $page;
                break;
            }
            if ($page->isActive()) {
                $this->view->previous = isset($previous) ? $previous : null;
                $getNext = true;
            }
            $previous = $page;
        }
    }

    public function indexAction ()
    {
        if (! $this->_isConfigWritable()) {
            throw new Rubedo\Exceptions\User('Local config file %1$s should be writable', "Exception29", $this->_localConfigFile);
        }
        if (! isset($this->_localConfig['installed']) || $this->_localConfig['installed']['status'] != 'finished') {
            if (! isset($this->_localConfig['installed']['action'])) {
                $this->_localConfig['installed']['action'] = 'start-wizard';
            }
            $action = $this->_localConfig['installed']['action'];
            $this->redirect($this->_helper->url($action));
        }
    }

    public function dropIndexesAction ()
    {
        Manager::getService('UrlCache')->drop();
        Manager::getService('Cache')->drop();
        $servicesArray = Rubedo\Interfaces\config::getCollectionServices();
        $result = array();
        foreach ($servicesArray as $service) {
            $result[] = Manager::getService($service)->dropIndexes();
        }
        $this->_helper->json($result);
    }

    public function startWizardAction ()
    {
        $this->_localConfig['installed'] = array(
            'status' => 'begin',
            'action' => 'start-wizard'
        );
        $this->view->displayMode = "start-wizard";
        $this->_saveLocalConfig();
    }

    public function finishWizardAction ()
    {
        $this->_localConfig['installed']['status'] = 'finished';
        
        $this->_saveLocalConfig();
        $this->_forward('index');
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
            if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
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

    /**
     * Check if a valid connection to MongoDB can be written in local config
     */
    public function setElasticSearchAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-elastic-search';
        }
        
        $esOptions = isset($this->_applicationOptions["searchstream"]["elastic"]) ? $this->_applicationOptions["searchstream"]["elastic"] : array();
        
        $dbForm = Install_Model_EsConfigForm::getForm($esOptions);
        
        try {
            if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
                $params = $dbForm->getValues();
                Rubedo\Elastic\DataAbstract::setOptions($params);
                $query = \Rubedo\Services\Manager::getService('ElasticDataIndex');
                $query->init();
            } else {
                $params = $esOptions;
                $query = \Rubedo\Services\Manager::getService('ElasticDataIndex');
                $query->init();
            }
            $connectionValid = true;
        } catch (Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->view->isReady = true;
            $this->_localConfig["searchstream"]["elastic"] = $params;
        } else {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'Rubedo can\'t connect itself to specified ES';
        }
        
        $this->view->form = $dbForm;
        
        $this->_saveLocalConfig();
    }

    public function setMailerAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-mailer';
        }
        
        $mailerOptions = isset($this->_applicationOptions["swiftmail"]["smtp"]) ? $this->_applicationOptions["swiftmail"]["smtp"] : array(
            'server' => null,
            'port' => null,
            'ssl' => null
        );
        
        $dbForm = Install_Model_MailConfigForm::getForm($mailerOptions);
        
        try {
            if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
                $params = $dbForm->getValues();
            } else {
                $params = $mailerOptions;
            }
            $transport = \Swift_SmtpTransport::newInstance($params['server'], $params['port'], $params['ssl'] ? 'ssl' : null);
            if (isset($params['username'])) {
                $transport->setUsername($params['username'])->setPassword($params['password']);
            }
            $transport->setTimeout(3);
            $transport->start();
            $transport->stop();
            $connectionValid = true;
        } catch (Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->view->isSet = true;
            $this->_localConfig["swiftmail"]["smtp"] = $params;
        } else {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'Rubedo can\'t connect to SMTP server';
        }
        $this->view->isReady = true;
        $this->view->form = $dbForm;
        
        $this->_saveLocalConfig();
    }

    public function setLocalDomainsAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-local-domains';
        }
        
        $dbForm = Install_Model_DomainAliasForm::getForm();
        
        if (! isset($this->_localConfig['site']['override'])) {
            $this->_localConfig['site']['override'] = array();
        }
        
        $key = $this->getParam('delete-domain');
        if ($key) {
            unset($this->_localConfig['site']['override'][$key]);
            $this->_saveLocalConfig();
        }
        
        if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
            $params = $dbForm->getValues();
            $overrideArray = array_values($this->_localConfig['site']['override']);
            if (in_array($params["localDomain"], $overrideArray)) {
                $this->view->hasError = true;
                $this->view->errorMsgs = "A domain can't be used to override twice.";
            } else {
                $this->_localConfig['site']['override'][$params["domain"]] = $params["localDomain"];
                $this->_saveLocalConfig();
            }
        }
        
        $this->view->isReady = true;
        
        $this->view->overrideList = $this->_localConfig['site']['override'];
        
        $this->view->form = $dbForm;
    }

    public function setPhpSettingsAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-php-settings';
        }
        
        $phpOptions = isset($this->_applicationOptions["phpSettings"]) ? $this->_applicationOptions["phpSettings"] : array();
        if (isset($this->_applicationOptions["resources"]["frontController"]["params"]["displayExceptions"])) {
            $phpOptions["displayExceptions"] = $this->_applicationOptions["resources"]["frontController"]["params"]["displayExceptions"];
        }
        
        if (isset($this->_applicationOptions["backoffice"]["extjs"]["debug"])) {
            $phpOptions["extDebug"] = $this->_applicationOptions["backoffice"]["extjs"]["debug"];
        }
        if (isset($this->_localConfig["authentication"]["authLifetime"])) {
            $phpOptions["authLifetime"] = $this->_localConfig["authentication"]["authLifetime"];
        }
        if (isset($this->_localConfig["resources"]["session"]["name"])) {
            $phpOptions["sessionName"] = $this->_localConfig["resources"]["session"]["name"];
        }
        
        $dbForm = Install_Model_PhpSettingsForm::getForm($phpOptions);
        
        if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
            $params = $dbForm->getValues();
            $this->_localConfig["resources"]["frontController"]["params"]["displayExceptions"] = $params["displayExceptions"];
            $this->_localConfig["backoffice"]["extjs"]["debug"] = $params["extDebug"];
            $this->_localConfig["authentication"]["authLifetime"] = $params["authLifetime"];
            $this->_localConfig["resources"]["session"]["name"] = $params["sessionName"];
            $params['display_startup_errors'] = $params['display_errors'];
            unset($params["displayExceptions"]);
            unset($params["extDebug"]);
            unset($params["authLifetime"]);
            unset($params["sessionName"]);
            // authentication.authLifetime
            // resources.session.name = rubedo
            $this->_localConfig["phpSettings"] = $params;
        }
        
        $connectionValid = true;
        
        $this->view->isReady = true;
        
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
        
        if ($this->getParam('doEnsureIndex', false)) {
            $this->view->isIndexed = $this->_doEnsureIndexes();
            if (! $this->view->isIndexed) {
                $this->view->shouldIndex = true;
            }
        } else {
            $this->view->shouldIndex = $this->_shouldIndex();
        }
        
        if ($this->getParam('initContents', false)) {
            $this->view->isContentsInitialized = $this->_doInsertContents();
        } else {
            $this->view->shouldInitialize = $this->_shouldInitialize();
        }
        
        if ($this->getParam('doInsertGroups', false)) {
            $this->view->groupCreated = $this->_docreateDefaultsGroup();
        }
        if ($this->_isDefaultGroupsExists() && ! $this->view->shouldIndex && ! $this->view->shouldInitialize) {
            $this->view->isReady = true;
        }
        
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
        
        if ($this->getRequest()->isPost() && $form->isValid($this->getAllParams())) {
            $params = $form->getValues();
            $hashService = \Rubedo\Services\Manager::getService('Hash');
            
            unset($params["confirmPassword"]);
            
            $params['salt'] = $hashService->generateRandomString();
            $params['password'] = $hashService->derivatePassword($params['password'], $params['salt']);
            $adminGroup = Manager::getService('Groups')->findByName('admin');
            $params['defaultGroup'] = $adminGroup['id'];
            $wasFiltered = AbstractCollection::disableUserFilter();
            $userService = Manager::getService('Users');
            $response = $userService->create($params);
            $result = $response['success'];
            
            AbstractCollection::disableUserFilter($wasFiltered);
            
            if (! $result) {
                $this->view->hasError = true;
                $this->view->errorMsg = $response['msg'];
            } else {
                $userId = $response['data']['id'];
                
                $groupService = Manager::getService('Groups');
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
        if (isset($options['port'])) {
            $connectionString .= ':' . $options['port'];
        }
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
            $localConfig = new Zend_Config_Json($this->_localConfigFile, null, array(
                'allowModifications' => true
            ));
        } elseif (is_file(APPLICATION_PATH . '/configs/local.ini')) {
            $localConfig = new Zend_Config_Ini(APPLICATION_PATH . '/configs/local.ini', null, array(
                'allowModifications' => true
            ));
        } else {
            $localConfig = new Zend_Config(array(), true);
        }
        $this->_localConfig = $localConfig->toArray();
    }

    protected function _doEnsureIndexes ()
    {
        Manager::getService('UrlCache')->drop();
        Manager::getService('Cache')->drop();
        $servicesArray = Rubedo\Interfaces\config::getCollectionServices();
        $result = true;
        foreach ($servicesArray as $service) {
            if (! Manager::getService($service)->checkIndexes()) {
                $result = $result && Manager::getService($service)->ensureIndexes();
            }
        }
        if ($result) {
            $this->_localConfig['installed']['index'] = $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db'];
            return true;
        } else {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'failed to apply indexes';
            return false;
        }
    }

    protected function _shouldIndex ()
    {
        if (isset($this->_applicationOptions['installed']['index']) && $this->_applicationOptions['installed']['index'] == $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function _shouldInitialize ()
    {
        if (isset($this->_applicationOptions['installed']['contents']) && $this->_applicationOptions['installed']['contents'] == $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function _docreateDefaultsGroup ()
    {
        if ($this->_isDefaultGroupsExists()) {
            return;
        }
        try {
            Manager::getService('Workspaces')->create(array(
                'text' => 'admin'
            ));
        } catch (Rubedo\Exceptions\User $exception) {
            // dont stop if already exists
        }
        $adminWorkspaceId = Manager::getService('Workspaces')->getAdminWorkspaceId();
        
        $success = true;
        $groupsJsonPath = APPLICATION_PATH . '/../data/default/groups';
        $groupsJson = new DirectoryIterator($groupsJsonPath);
        foreach ($groupsJson as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            if ($file->getExtension() == 'json') {
                $itemJson = file_get_contents($file->getPathname());
                $item = Zend_Json::decode($itemJson);
                if ($item['name'] == 'admin') {
                    $item['workspace'] = $adminWorkspaceId;
                    $item['inheritWorkspace'] = false;
                }
                $result = Manager::getService('Groups')->create($item);
                $success = $result['success'] && $success;
            }
        }
        if (! $success) {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'failed to create default groups';
        } else {
            $this->view->isGroupsCreated = true;
        }
        
        return $success;
    }

    protected function _isDefaultGroupsExists ()
    {
        $adminGroup = Manager::getService('Groups')->findByName('admin');
        $publicGroup = Manager::getService('Groups')->findByName('public');
        $result = ! is_null($adminGroup) && ! is_null($publicGroup);
        $this->view->isDefaultGroupsExists = $result;
        return $result;
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
                    try {
                        if (! Manager::getService($collection)->findOne(Filter::Factory('Value')->setName('defaultId')
                            ->setValue($item['defaultId']))) {
                            $result = Manager::getService($collection)->create($item);
                        } else {
                            $result['success'] = true;
                        }
                    } catch (Rubedo\Exceptions\User $exception) {
                        $result['success'] = true;
                    }
                    
                    $success = $result['success'] && $success;
                }
            }
        }
        
        if (! $success) {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'failed to initialize contents';
        } else {
            $this->_localConfig['installed']['contents'] = $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db'];
            $this->view->isContentInitialized = true;
        }
        
        return $success;
    }
}

