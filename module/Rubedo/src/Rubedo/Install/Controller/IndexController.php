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
namespace Rubedo\Install\Controller;

use Rubedo\Mongo\DataAccess;
use Rubedo\Collection\AbstractCollection;
use Rubedo\Services\Manager;
use Rubedo\Elastic\DataAbstract;
use WebTales\MongoFilters\Filter;
use Rubedo\Update\Install;
use Rubedo\Install\Model\NavObject;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Rubedo\Install\Model\DbConfigForm;



/**
 * Installer Controller
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class IndexController extends AbstractActionController
{

    protected $localConfigDir;

    protected $localConfigFile;

    protected $_localConfig;

    protected $_applicationOptions = array();
    
    protected $installObject;
    

    public function __construct()
    {
        \Rubedo\User\CurrentUser::setIsInstallerUser(true);
        
        AbstractCollection::disableUserFilter();
        
        $this->navigation = NavObject::getNav();
        $this->setWizardSteps();
        
        $this->installObject = new Install();
        if (! $this->installObject->isConfigWritable()) {
            throw new \Rubedo\Exceptions\User('Local config file %1$s should be writable', "Exception29", $this->localConfigFile);
        }

        $this->installObject->loadLocalConfig();
        
        $this->viewData = new \stdClass();
        
    }

    /**
     * get previous and next step for wizard
     */
    protected function setWizardSteps()
    {
        $getNext = false;
        $previous = null;
        foreach ($this->navigation as $page) {
            if ($getNext) {
                $this->viewData->next = $page;
                break;
            }
            if ($page->isActive()) {
                $this->viewData->previous = isset($previous) ? $previous : null;
                $getNext = true;
            }
            $previous = $page;
        }
    }

    public function indexAction()
    {
        $this->layout('layout/install');
        $config = $this->installObject->getLocalConfig();
        if (! isset($config['installed']) || $config['installed']['status'] != 'finished') {
            if (! isset($config['installed']['action'])) {
                $config['installed']['action'] = 'start-wizard';
            }
            $redirectParams = array(
                'controller'=>'index',
                'action' => $config['installed']['action'],
            );
            return $this->redirect()->toRoute('install/default', $redirectParams);
        }

        $this->viewDataModel = new ViewModel(array(
            'navigationContainer' => $this->navigation,
            'localConfigFile'=> $this->installObject->getConfigFilePath()
        ));
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/index');
        return $this->viewDataModel;
    }

    public function dropIndexesAction()
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

    public function startWizardAction()
    {
        $this->layout('layout/install');
        $config = $this->installObject->getLocalConfig();
        $config['installed'] = array(
            'status' => 'begin',
            'action' => 'start-wizard'
        );
        $this->viewData = array('displayMode' => "start-wizard");
        $this->installObject->saveLocalConfig($config);
        $this->viewDataModel = new ViewModel($this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/start-wizard');
        return $this->viewDataModel;
    }

    public function finishWizardAction()
    {
        $config['installed']['status'] = 'finished';
        
        $this->installObject->saveLocalConfig();
        $this->_forward('index');
    }

    /**
     * Check if a valid connection to MongoDB can be written in local config
     */
    public function setDbAction()
    {
        $this->viewData->displayMode = 'regular';
        $config = $this->installObject->getLocalConfig();
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-db';
        }
        
        $mongoOptions = isset($config["datastream"]["mongo"]) ? $config["datastream"]["mongo"] : array();
        
        $dbForm = DbConfigForm::getForm($mongoOptions);
        
        $mongoAccess = new DataAccess();
        
        try {
            $dbForm->setData($this->params()->fromPost());
            if ($this->getRequest()->isPost() && $dbForm->isValid()) {
                $params = $dbForm->getData();
                $mongo = $this->_buildConnectionString($params);
                $dbName = $params['db'];
                $initCollection = $mongoAccess->init('Users', $dbName, $mongo);
                $connectionValid = true;
            } else {
                $initCollection = $mongoAccess->init('Users');
                if(isset($config["datastream"]["mongo"])){
                    $params = $config["datastream"]["mongo"];
                    $connectionValid = true;
                }else{
                    $params = array();
                    $connectionValid = false;
                }
            }
            
        } catch (\Exception $exception) {
            throw $exception;
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->viewData->isReady = true;
            $config["datastream"]["mongo"] = $params;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect itself to specified DB';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->installObject->saveLocalConfig($config);
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array)$this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-db');
        return $this->viewDataModel;
        
    }

    /**
     * Check if a valid connection to MongoDB can be written in local config
     */
    public function setElasticSearchAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-elastic-search';
        }
        
        $esOptions = isset($this->_applicationOptions["searchstream"]["elastic"]) ? $this->_applicationOptions["searchstream"]["elastic"] : array();
        
        $dbForm = Install_Model_EsConfigForm::getForm($esOptions);
        
        try {
            if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
                $params = $dbForm->getValues();
                DataAbstract::setOptions($params);
                $query = \Rubedo\Services\Manager::getService('ElasticDataIndex');
                $query->init();
            } else {
                $params = $esOptions;
                $query = \Rubedo\Services\Manager::getService('ElasticDataIndex');
                $query->init();
            }
            $connectionValid = true;
        } catch (\Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->viewData->isReady = true;
            $config["searchstream"]["elastic"] = $params;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect itself to specified ES';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->installObject->saveLocalConfig();
    }

    /**
     * loadLanguages and define default and Active Languages
     */
    public function defineLanguagesAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'define-languages';
        }
        
        $params = array();
        $ok = false;
        
        $languageService = Manager::getService('Languages');
        
        $defaultLocale = $languageService->getDefaultLanguage();
        if ($defaultLocale) {
            $ok = true;
        }
        
        $languageListResult = $languageService->getList(null, array(
            array(
                'property' => 'label',
                'direction' => 'asc'
            )
        ));
        if ($languageListResult['count'] == 0) {
            Install::importLanguages();
            $languageListResult = $languageService->getList(null, array(
                array(
                    'property' => 'label',
                    'direction' => 'asc'
                )
            ));
        }
        $languageList = $languageListResult["data"];
        $languageSelectList = array();
        $languageSelectList[] = "";
        
        foreach ($languageList as $value) {
            list ($label) = explode(';', $value['label']);
            $languageSelectList[$value['locale']] = isset($value['ownLabel']) && ! empty($value['ownLabel']) ? $value['ownLabel'] : $label;
        }
        
        $params['languages'] = $languageSelectList;
        $params['defaultLanguage'] = isset($defaultLocale) ? $defaultLocale : 'en';
        
        $dbForm = Install_Model_LanguagesConfigForm::getForm($params);
        
        if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
            $values = $dbForm->getValues();
            $update = install::setDefaultRubedoLanguage($values['defaultLanguage']);
            if ($update) {
                $ok = true;
            }
        }
        
        if ($ok) {
            $this->viewData->isReady = true;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'A default language should be activated';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->installObject->saveLocalConfig();
    }

    public function setMailerAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-mailer';
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
        } catch (\Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->viewData->isSet = true;
            $config["swiftmail"]["smtp"] = $params;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect to SMTP server';
        }
        $this->viewData->isReady = true;
        $this->viewData->form = $dbForm;
        
        $this->installObject->saveLocalConfig();
    }

    public function setLocalDomainsAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-local-domains';
        }
        
        $dbForm = Install_Model_DomainAliasForm::getForm();
        
        if (! isset($config['site']['override'])) {
            $config['site']['override'] = array();
        }
        
        $key = $this->getParam('delete-domain');
        if ($key) {
            unset($config['site']['override'][$key]);
            $this->installObject->saveLocalConfig();
        }
        
        if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
            $params = $dbForm->getValues();
            $overrideArray = array_values($config['site']['override']);
            if (in_array($params["localDomain"], $overrideArray)) {
                $this->viewData->hasError = true;
                $this->viewData->errorMsgs = "A domain can't be used to override twice.";
            } else {
                $config['site']['override'][$params["domain"]] = $params["localDomain"];
                $this->installObject->saveLocalConfig();
            }
        }
        
        $this->viewData->isReady = true;
        
        $this->viewData->overrideList = $config['site']['override'];
        
        $this->viewData->form = $dbForm;
    }

    public function setPhpSettingsAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-php-settings';
        }
        
        $phpOptions = isset($this->_applicationOptions["phpSettings"]) ? $this->_applicationOptions["phpSettings"] : array();
        if (isset($this->_applicationOptions["resources"]["frontController"]["params"]["displayExceptions"])) {
            $phpOptions["displayExceptions"] = $this->_applicationOptions["resources"]["frontController"]["params"]["displayExceptions"];
        }
        
        if (isset($this->_applicationOptions["backoffice"]["extjs"]["debug"])) {
            $phpOptions["extDebug"] = $this->_applicationOptions["backoffice"]["extjs"]["debug"];
        }
        if (isset($config["authentication"]["authLifetime"])) {
            $phpOptions["authLifetime"] = $config["authentication"]["authLifetime"];
        }
        if (isset($config["resources"]["session"]["name"])) {
            $phpOptions["sessionName"] = $config["resources"]["session"]["name"];
        }
        
        $dbForm = Install_Model_PhpSettingsForm::getForm($phpOptions);
        
        if ($this->getRequest()->isPost() && $dbForm->isValid($this->getAllParams())) {
            $params = $dbForm->getValues();
            $config["resources"]["frontController"]["params"]["displayExceptions"] = $params["displayExceptions"];
            $config["backoffice"]["extjs"]["debug"] = $params["extDebug"];
            $config["authentication"]["authLifetime"] = $params["authLifetime"];
            $config["resources"]["session"]["name"] = $params["sessionName"];
            $params['display_startup_errors'] = $params['display_errors'];
            unset($params["displayExceptions"]);
            unset($params["extDebug"]);
            unset($params["authLifetime"]);
            unset($params["sessionName"]);
            // authentication.authLifetime
            // resources.session.name = rubedo
            $config["phpSettings"] = $params;
        }
        
        $this->viewData->isReady = true;
        
        $this->viewData->form = $dbForm;
        
        $this->installObject->saveLocalConfig();
    }

    public function setDbContentsAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-db-contents';
        }
        
        if ($this->getParam('doEnsureIndex', false)) {
            $this->viewData->isIndexed = $this->_doEnsureIndexes();
            if (! $this->viewData->isIndexed) {
                $this->viewData->shouldIndex = true;
            }
        } else {
            $this->viewData->shouldIndex = $this->_shouldIndex();
        }
        
        if ($this->getParam('initContents', false)) {
            $this->viewData->isContentsInitialized = $this->_doInsertContents();
        } else {
            $this->viewData->shouldInitialize = $this->_shouldInitialize();
        }
        
        if ($this->getParam('doInsertGroups', false)) {
            $this->viewData->groupCreated = $this->_docreateDefaultsGroup();
        }
        if ($this->_isDefaultGroupsExists() && ! $this->viewData->shouldIndex && ! $this->viewData->shouldInitialize) {
            $this->viewData->isReady = true;
        }
        
        $this->installObject->saveLocalConfig();
    }

    public function setAdminAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $config['installed']['action'] = 'set-admin';
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
                $this->viewData->hasError = true;
                $this->viewData->errorMsg = $response['msg'];
            } else {
                $userId = $response['data']['id'];
                
                $groupService = Manager::getService('Groups');
                $adminGroup['members'][] = $userId;
                $groupService->update($adminGroup);
                $this->viewData->accountName = $params['name'];
            }
            
            $this->viewData->creationDone = $result;
        }
        
        $listAdminUsers = Manager::getService('Users')->getAdminUsers();
        
        if ($listAdminUsers['count'] > 0) {
            $this->viewData->hasAdmin = true;
            $this->viewData->adminAccounts = $listAdminUsers['data'];
            $this->viewData->isReady = true;
        } else {
            
            $this->viewData->errorMsgs = 'No Admin Account Set';
        }
        
        $this->viewData->form = $form;
        
        $this->installObject->saveLocalConfig();
    }

    protected function _buildConnectionString($options)
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


    protected function _doEnsureIndexes()
    {
        Manager::getService('UrlCache')->drop();
        Manager::getService('Cache')->drop();
        $servicesArray = \Rubedo\Interfaces\config::getCollectionServices();
        $result = true;
        foreach ($servicesArray as $service) {
            if (! Manager::getService($service)->checkIndexes()) {
                $result = $result && Manager::getService($service)->ensureIndexes();
            }
        }
        if ($result) {
            $config['installed']['index'] = $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db'];
            return true;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'failed to apply indexes';
            return false;
        }
    }

    protected function _shouldIndex()
    {
        if (isset($this->_applicationOptions['installed']['index']) && $this->_applicationOptions['installed']['index'] == $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function _shouldInitialize()
    {
        if (isset($this->_applicationOptions['installed']['contents']) && $this->_applicationOptions['installed']['contents'] == $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function _docreateDefaultsGroup()
    {
        if ($this->_isDefaultGroupsExists()) {
            return;
        }
        $success = \Rubedo\Update\Install::doCreateDefaultsGroups();
        if (! $success) {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'failed to create default groups';
        } else {
            $this->viewData->isGroupsCreated = true;
        }
        
        return $success;
    }

    protected function _isDefaultGroupsExists()
    {
        $adminGroup = Manager::getService('Groups')->findByName('admin');
        $publicGroup = Manager::getService('Groups')->findByName('public');
        $result = ! is_null($adminGroup) && ! is_null($publicGroup);
        $this->viewData->isDefaultGroupsExists = $result;
        return $result;
    }

    protected function _doInsertContents()
    {
        $success = \Rubedo\Update\Install::doInsertContents();
        
        if ($success) {
            \Rubedo\Update\Update::update();
            \Rubedo\Collection\Pages::localizeAllCollection();
        }
        
        if (! $success) {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'failed to initialize contents';
        } else {
            $config['installed']['contents'] = $this->_applicationOptions["datastream"]["mongo"]["server"] . '/' . $this->_applicationOptions["datastream"]["mongo"]['db'];
            $this->viewData->isContentInitialized = true;
        }
        
        return $success;
    }
}

