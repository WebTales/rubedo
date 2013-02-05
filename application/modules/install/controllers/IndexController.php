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
            throw new Rubedo\Exceptions\User('Local config file ' . $this->_localConfigFile . ' should be writable');
        }
        if (! isset($this->_localConfig['installed']) || $this->_localConfig['installed']['status'] != 'finished') {
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

    public function setDbAction ()
    {
        $this->view->displayMode = 'regular';
        if ($this->_localConfig['installed']['status'] != 'finished') {
            $this->view->displayMode = "wizard";
            $this->_localConfig['installed']['action'] = 'set-db';
        }
        
        $mongoOptions = $this->_applicationOptions["datastream"]["mongo"];
        
        $serverNameField = new Zend_Form_Element_Text('server');
        $serverNameField->setRequired(true);
        $serverNameField->setValue(isset($mongoOptions['server']) ? $mongoOptions['server'] : 'localhost/rubedo');
        $serverNameField->setLabel('Server Name');
        
        $serverPortField = new Zend_Form_Element_Text('serverport');
        // $serverPortField->setRequired(true);
        $serverPortField->setValue(isset($mongoOptions['port']) ? $mongoOptions['port'] : null);
        $serverPortField->addValidator('digits');
        $serverPortField->setLabel('Server Port');
        
        $dbNameField = new Zend_Form_Element_Text('db');
        $dbNameField->setRequired(true);
        $dbNameField->setValue(isset($mongoOptions['db']) ? $mongoOptions['db'] : null);
        $dbNameField->setLabel('Db Name');
        
        $serverLoginField = new Zend_Form_Element_Text('login');
        $serverLoginField->setValue(isset($mongoOptions['login']) ? $mongoOptions['login'] : null);
        $serverLoginField->setLabel('Username');
        
        $serverPasswordField = new Zend_Form_Element_Text('password');
        $serverPasswordField->setValue(isset($mongoOptions['password']) ? $mongoOptions['password'] : null);
        $serverPasswordField->setLabel('Password');
        
        $submitButton = new Zend_Form_Element_Submit('Submit');
        $submitButton->setAttrib('class', 'btn btn-large btn-primary');
        
        $dbForm = new Zend_Form();
        $dbForm->setMethod('post');
        $dbForm->addElement($serverNameField);
        // $dbForm->addElement($serverPortField);
        $dbForm->addElement($dbNameField);
        $dbForm->addElement($serverLoginField);
        $dbForm->addElement($serverPasswordField);
        $dbForm->addElement($submitButton);
        
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
            $this->_localConfig["datastream"]["mongo"]=$params;
        } else {
            $this->view->hasError = true;
            $this->view->errorMsgs = 'Rubedo can\'t connect itself to specified DB';
        }
        
        $this->view->form = $dbForm;
        
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
}

