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
use Rubedo\Update\Install;
use Rubedo\Install\Model\NavObject;
use Rubedo\Install\Model\DbConfigForm;
use Rubedo\Install\Model\EsConfigForm;
use Rubedo\Install\Model\LanguagesConfigForm;
use Rubedo\Install\Model\AdminConfigForm;
use Rubedo\Install\Model\MailConfigForm;
use Rubedo\Install\Model\DomainAliasForm;
use Rubedo\Install\Model\PhpSettingsForm;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use WebTales\MongoFilters\Filter;


/**
 * Installer
 * Controller
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

    protected $config;

    protected $_applicationOptions = array();

    protected $installObject;

    protected $viewData;

    /**
     * Init installation context
     *
     * Set a navigation for install tool screens
     * Set the installation object
     *
     * @throws \Rubedo\Exceptions\User
     */
    public function __construct()
    {
        $this->viewData = new \stdClass();
        \Rubedo\User\CurrentUser::setIsInstallerUser(true);
        
        AbstractCollection::disableUserFilter();
        
        $this->navigation = NavObject::getNav();
        $this->setWizardSteps();
        $this->viewData->navigationContainer = $this->navigation;
        
        $this->installObject = new Install();
        if (! $this->installObject->isConfigWritable()) {
            throw new \Rubedo\Exceptions\User('Local config file %1$s should be writable', "Exception29", $this->localConfigFile);
        }
        
        $this->installObject->loadLocalConfig();
        $this->config = $this->installObject->getLocalConfig();
        if (! isset($this->config['installed'])) {
            $this->config['installed'] = array();
        }
    }

    /**
     * set the previous and next page from navigation and current page
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
        $this->config = $this->installObject->getLocalConfig();
        if (! isset($this->config['installed']) || $this->config['installed']['status'] != 'finished') {
            if (! isset($this->config['installed']['action'])) {
                $this->config['installed']['action'] = 'start-wizard';
                $this->installObject->saveLocalConfig($this->config);
            }
            $redirectParams = array(
                'controller' => 'index',
                'action' => $this->config['installed']['action']
            );
            return $this->redirect()->toRoute('install', $redirectParams);
        }
        
        $this->viewData->localConfigFile = $this->installObject->getConfigFilePath();
        
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/index');
        return $this->viewDataModel;
    }

    public function dropIndexesAction()
    {
        Manager::getService('UrlCache')->drop();
        Manager::getService('Cache')->drop();
        $servicesArray = \Rubedo\Interfaces\config::getCollectionServices();
        $result = array();
        foreach ($servicesArray as $service) {
            $result[] = Manager::getService($service)->dropIndexes();
        }
        return new JsonModel($result);
    }

    public function startWizardAction()
    {
        $this->layout('layout/install');
        
        $this->config['installed'] = array(
            'status' => 'begin',
            'action' => 'start-wizard'
        );
        $this->viewData->displayMode = "start-wizard";
        $this->installObject->saveLocalConfig($this->config);
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/start-wizard');
        return $this->viewDataModel;
    }

    public function finishWizardAction()
    {
        $this->config = $this->installObject->getLocalConfig();
        $this->config['installed']['status'] = 'finished';
        $this->installObject->saveLocalConfig($this->config);
        $redirectParams = array(
            'controller' => 'index',
            'action' => 'index'
        );
        return $this->redirect()->toRoute('install', $redirectParams);
    }

    /**
     * Set DB configuration to configfile
     */
    public function setDbAction()
    {
        $this->viewData->displayMode = 'regular';
        $this->viewData->isReady = false;
        $this->config = $this->installObject->getLocalConfig();
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-db';
            $this->installObject->saveLocalConfig($this->config);
        }
        
        $mongoOptions = isset($this->config["datastream"]["mongo"]) ? $this->config["datastream"]["mongo"] : array();
        
        $dbForm = DbConfigForm::getForm($mongoOptions);
        
        $mongoAccess = new DataAccess();
        
        try {
            $dbForm->setData($this->params()
                ->fromPost());
            if ($this->getRequest()->isPost() && $dbForm->isValid()) {
                $params = $dbForm->getData();
                unset($params['buttonGroup']);
                $mongo = $this->buildConnectionString($params);
                $dbName = $params['db'];
                $initCollection = $mongoAccess->init('Users', $dbName, $mongo);
                $connectionValid = true;
            } else {
                $initCollection = $mongoAccess->init('Users');
                if (isset($this->config["datastream"]["mongo"])) {
                    $params = $this->config["datastream"]["mongo"];
                    $connectionValid = true;
                } else {
                    $params = array();
                    $connectionValid = false;
                }
            }
        } catch (\Exception $exception) {
            $connectionValid = false;
        }
        if ($connectionValid) {
            $this->viewData->isReady = true;
            $this->config["datastream"]["mongo"] = $params;
            $this->installObject->saveLocalConfig($this->config);
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect itself to specified DB';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-db');
        return $this->viewDataModel;
    }

    /**
     * Check if a valid connection to Elasticsearch can be written in local config
     */
    public function setElasticSearchAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-elastic-search';
            $this->installObject->saveLocalConfig($this->config);
        }
        
        $esOptions = isset($this->config["elastic"]) ? $this->config["elastic"] : array();
        
        $dbForm = EsConfigForm::getForm($esOptions);
        $dbForm->setData($this->params()
            ->fromPost());
        try {
            if ($this->getRequest()->isPost() && $dbForm->isValid()) {
                $params = $dbForm->getData();
                unset($params['buttonGroup']);
                DataAbstract::lazyLoadConfig();
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
            $this->config["elastic"] = $params;
            $this->installObject->saveLocalConfig($this->config);
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect itself to specified ES';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-elastic-search');
        return $this->viewDataModel;
    }

    /**
     * load Languages and define default and Active Languages
     */
    public function defineLanguagesAction()
    {
        $this->viewData->displayMode = 'regular';
        $this->viewData->isReady = false;
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'define-languages';
            $this->installObject->saveLocalConfig($this->config);
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
        
        $dbForm = LanguagesConfigForm::getForm($params);
        $dbForm->setData($this->params()
            ->fromPost());
        if ($this->getRequest()->isPost() && $dbForm->isValid()) {
            $params = $dbForm->getData();
            unset($params['buttonGroup']);
            $update = $this->installObject->setDefaultRubedoLanguage($params['defaultLanguage']);
            if ($update) {
                $ok = true;
            }
        }
        
        if ($ok) {
            $this->viewData->isReady = true;
            $this->installObject->saveLocalConfig($this->config);
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'A default language should be activated';
        }
        
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/define-languages');
        return $this->viewDataModel;
    }

    public function setMailerAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-mailer';
            $this->installObject->saveLocalConfig($this->config);
        }
        
        $mailerOptions = isset($this->config["swiftmail"]["smtp"]) ? $this->config["swiftmail"]["smtp"] : array(
            'server' => null,
            'port' => null,
            'ssl' => null
        );
        
        $dbForm = MailConfigForm::getForm($mailerOptions);
        $dbForm->setData($this->params()
            ->fromPost());
        
        try {
            if ($this->getRequest()->isPost() && $dbForm->isValid()) {
                $params = $dbForm->getData();
                unset($params['buttonGroup']);
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
            $this->config["swiftmail"]["smtp"] = $params;
            $this->installObject->saveLocalConfig($this->config);
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'Rubedo can\'t connect to SMTP server';
        }
        $this->viewData->isReady = true;
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-mailer');
        return $this->viewDataModel;
    }

    public function setLocalDomainsAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-local-domains';
        }
        
        $dbForm = DomainAliasForm::getForm();
        
        if (! isset($this->config['site']['override'])) {
            $this->config['site']['override'] = array();
        }
        
        $key = $this->params()->fromQuery('delete-domain');
        if ($key) {
            unset($this->config['site']['override'][$key]);
            $this->installObject->saveLocalConfig();
        }
        $dbForm->setData($this->params()
            ->fromPost());
        $formUrl = $this->url()->fromRoute('install', array(
            'controller' => 'index',
            'action' => 'set-local-domains'
        ));
        $dbForm->setAttribute('action', $formUrl);
        if ($this->getRequest()->isPost() && $dbForm->isValid()) {
            $params = $dbForm->getData();
            unset($params['buttonGroup']);
            $overrideArray = array_values($this->config['site']['override']);
            if (in_array($params["localDomain"], $overrideArray)) {
                $this->viewData->hasError = true;
                $this->viewData->errorMsgs = "A domain can't be used to override twice.";
            } else {
                $this->config['site']['override'][$params["domain"]] = $params["localDomain"];
                $this->installObject->saveLocalConfig($this->config);
            }
        }
        
        $this->viewData->isReady = true;
        
        $this->viewData->overrideList = $this->config['site']['override'];
        
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-local-domains');
        return $this->viewDataModel;
    }

    public function setPhpSettingsAction()
    {
        $this->viewData->displayMode = 'regular';
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-php-settings';
            $this->installObject->saveLocalConfig($this->config);
        }
        
        $applicationConfig = Manager::getService('application')->getConfig();
        
        $dbForm = PhpSettingsForm::getForm($applicationConfig);
        $dbForm->setData($this->params()
            ->fromPost());
        
        if ($this->getRequest()->isPost() && $dbForm->isValid()) {
            $params = $dbForm->getData();
            unset($params['buttonGroup']);
            $params['session']['remember_me_seconds'] = $params['session']["authLifetime"];
            $params['session']['cookie_httponly'] = $params['session']["authLifetime"];
            unset($params["session"]["authLifetime"]);
            $this->config = array_merge($this->config, $params);
            $this->installObject->saveLocalConfig($this->config);
        }
        
        $this->viewData->isReady = true;
        
        $this->viewData->form = $dbForm;
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-php-settings');
        return $this->viewDataModel;
    }

    public function setDbContentsAction()
    {
        $this->viewData->displayMode = 'regular';
        $this->viewData->isReady = false;
        $this->viewData->shouldIndex = false;
        $this->viewData->shouldInitialize = false;
        
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-db-contents';
        }
        
        if ($this->params()->fromQuery('doEnsureIndex', false)) {
            $this->viewData->isIndexed = $this->doEnsureIndexes();
            if (! $this->viewData->isIndexed) {
                $this->viewData->shouldIndex = true;
            }
        } else {
            $this->viewData->shouldIndex = $this->shouldIndex();
        }
        
        if ($this->params()->fromQuery('initContents', false)) {
            $this->viewData->isContentsInitialized = $this->doInsertContents();
        } else {
            $this->viewData->shouldInitialize = $this->shouldInitialize();
        }
        
        if ($this->params()->fromQuery('doInsertGroups', false)) {
            $this->viewData->groupCreated = $this->docreateDefaultsGroup();
        }
        if ($this->isDefaultGroupsExists() && ! $this->viewData->shouldIndex && ! $this->viewData->shouldInitialize) {
            $this->viewData->isReady = true;
        }
        
        $this->installObject->saveLocalConfig($this->config);
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-db-contents');
        return $this->viewDataModel;
    }

    public function setAdminAction()
    {
        $this->viewData->displayMode = 'regular';
        $this->viewData->isReady = false;
        if ($this->config['installed']['status'] != 'finished') {
            $this->viewData->displayMode = "wizard";
            $this->config['installed']['action'] = 'set-admin';
        }
        
        $form = AdminConfigForm::getForm();
        $form->setData($this->params()
            ->fromPost());
        
        if ($this->getRequest()->isPost() && $form->isValid()) {
            
            $params = $form->getData();
            unset($params['buttonGroup']);
            $hashService = \Rubedo\Services\Manager::getService('Hash');
            
            unset($params["confirmPassword"]);
            
            $params['salt'] = $hashService->generateRandomString();
            $params['password'] = $hashService->derivatePassword($params['password'], $params['salt']);
            $adminGroup = Manager::getService('Groups')->findByName('admin');
            $params['defaultGroup'] = $adminGroup['id'];
            $filters=Filter::factory();
            $filters->addFilter(Filter::factory('Value')->setName('UTType')
                ->setValue("default"));
            $defaultUserType=Manager::getService("UserTypes")->findOne($filters);
            $params['type']=$defaultUserType["id"];
            $params['fields']=array();
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
        
        $this->layout('layout/install');
        $this->viewDataModel = new ViewModel((array) $this->viewData);
        $this->viewDataModel->setTemplate('rubedo/install/controller/index/set-admin');
        return $this->viewDataModel;
    }

    protected function buildConnectionString($options)
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

    protected function doEnsureIndexes()
    {
        $result = $this->installObject->doEnsureIndexes();
        if ($result) {
            $this->config['installed']['index'] = $this->config["datastream"]["mongo"]["server"] . '/' . $this->config["datastream"]["mongo"]['db'];
            return true;
        } else {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'failed to apply indexes';
            return false;
        }
    }

    protected function shouldIndex()
    {
        if (isset($this->config['installed']['index']) && $this->config['installed']['index'] == $this->config["datastream"]["mongo"]["server"] . '/' . $this->config["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function shouldInitialize()
    {
        if (isset($this->config['installed']['contents']) && $this->config['installed']['contents'] == $this->config["datastream"]["mongo"]["server"] . '/' . $this->config["datastream"]["mongo"]['db']) {
            return false;
        } else {
            return true;
        }
    }

    protected function docreateDefaultsGroup()
    {
        if ($this->isDefaultGroupsExists()) {
            return;
        } else {
            $this->viewData->isDefaultGroupsExists = false;
        }
        $success = $this->installObject->doCreateDefaultsGroups();
        if (! $success) {
            $this->viewData->hasError = true;
            $this->viewData->errorMsgs = 'failed to create default groups';
        } else {
            $this->viewData->isGroupsCreated = true;
        }
        
        return $success;
    }

    protected function isDefaultGroupsExists()
    {
        $result = $this->installObject->isDefaultGroupsExists();
        $this->viewData->isDefaultGroupsExists = $result;
        return $result;
    }

    protected function doInsertContents()
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
            $this->config['installed']['contents'] = $this->config["datastream"]["mongo"]["server"] . '/' . $this->config["datastream"]["mongo"]['db'];
            $this->viewData->isContentInitialized = true;
        }
        
        return $success;
    }
}

