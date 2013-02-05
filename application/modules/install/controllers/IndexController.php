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
    }

    public function indexAction ()
    {
        if (! $this->_isConfigWritable()) {
            throw new Rubedo\Exceptions\User(
                    'Local config file ' . $this->_localConfigFile .
                             ' should be writable');
        }
        if (! isset($this->_localConfig['installed'])) {
            $this->_localConfig['installed'] = array(
                    'status' => 'begin'
            );
        }
        
        $this->_saveLocalConfig();
    }
    
    public function setDbAction(){
        
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
            $localConfig = new Zend_Config_Json($this->_localConfigFile, 
                    null, 
                    array(
                            'allowModifications' => true
                    ));
        } else {
            $localConfig = new Zend_Config(array(), true);
        }
        $this->_localConfig = $localConfig->toArray();
    }
}

