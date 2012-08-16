<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */
namespace Rubedo\Services;

use Rubedo, Rubedo\Interfaces, Rubedo\Interfaces\Services\IServicesManager;

/**
 * Service Manager Interface
 *
 * Proxy to actual services, offer a static getService and handle dependancy
 * injection
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
final class Manager implements IServicesManager
{


    /**
     * Injected Dependancy : service object
     *
     * @var object
     */
    protected $_object;

    /**
     * array of current service parameters
     *
     * @var array
     */
    protected static $_servicesOptions;

    /**
     * current service name
     *
     * @var string
     */
    protected $_serviceName;

    /**
     * Setter of services parameters, to init them from bootstrap
     *
     * @param array $options
     */
    public static function setOptions ($options)
    {
        if ('array' !== gettype($options)) {
            throw new \Rubedo\Exceptions\ServiceManager('Services parameters should be an array');
        }
        self::$_servicesOptions = $options;
    }

    /**
     * getter of services
     *
     * @return array array of all the services
     */
    public static function getOptions ()
    {
        return self::$_servicesOptions;
    }

    /**
     * Getter of the current service parameters or the specified parameter
     *
     * @param string $name optionnal parameter name
     * @return mixed value or array of valuefor asked parameter
     */
    public function getCurrentOptions ($name = null)
    {
        if (gettype($name) !== 'string' && $name !== null) {
            throw new \Exception('Manager->getCurrentOptions only accept string argument');
        }
        if ($name == null) {
            return $this->_currentOptions;
        }
        
        if (isset($this->_currentOptions[$name])) {
            return $this->_currentOptions[$name];
        }
        return null;
    }

    /**
     * Getter of the current service name
     *
     * @return string
     */
    public function getServiceName ()
    {
        return $this->_serviceName;
    }

    /**
     * protected constructor : create manager object and nested service object
     *
     * @param string $serviceClassName Name of nested class
     * @param string $serviceName Name of the service
     */
    protected function __construct ($serviceClassName, $serviceName)
    {
        $this->setServiceObj(new $serviceClassName());
        $this->_serviceName = $serviceName;
        
        if (isset(self::$_servicesOptions[$serviceName])) {
            $this->_currentOptions = self::$_servicesOptions[$serviceName];
        } else {
            $this->_currentOptions = array();
        }
    }

    /**
     * Dependancy setter : set the nested object and inform the nested object
     * of the manager instance
     *
     * @param object $obj
     */
    protected function setServiceObj ($obj)
    {
        $this->_object = $obj;
        $this->_object->_service = $this;
    }
    
     /**
     * Dependancy getter : get the nested object and inform the nested object
     * of the manager instance
     *
     * @return object $obj
     */
    public function getServiceObj ()
    {
        return $this->_object;
    }

    /**
     * Public static method to get an instance of the service given by its
     * name
     *
     * Return an instance of the manager containing the actual service object
     *
     * @param string $serviceName name of the service
     * @return static instance of the manager
     */
    public static function getService ($serviceName)
    {
        if (gettype($serviceName) !== 'string') {
            throw new \Rubedo\Exceptions\ServiceManager('getService only accept string argument');
        }
        
        $serviceName = ucfirst($serviceName);
        
        $serviceClassName = self::resolveName($serviceName);
        
        return new self($serviceClassName, $serviceName);
    }

    /**
     * Resolve the service name to the service class name for dependancy
     * injection
     *
     * @param string $serviceName name of the service
     * @return string class to instanciate
     * @todo read dependancy from options
     */
    protected static function resolveName ($serviceName)
    {
        $options = self::$_servicesOptions;
        
        if (isset($options[$serviceName]['class'])) {
            $className = $options[$serviceName]['class'];
        } else {
            throw new \Rubedo\Exceptions\ServiceManager('Classe name for ' . $serviceName . ' service should be defined in config file');
        }
        if (! $interfaceName = Rubedo\Interfaces\config::getInterface($serviceName)) {
            throw new \Rubedo\Exceptions\ServiceManager($serviceName . ' isn\'t declared in service interface config');
        }
        if (! in_array($interfaceName, class_implements($className))) {
            throw new \Exception($className . ' don\'t implement ' . $interfaceName);
        }
        
        return $className;
    }

    /**
     *
     *
     *
     *
     * Call : magic method invoke when calling a none existing manager method,
     * proxy to the service object
     *
     * @param string $name service method name
     * @param array $arguments service method array of arguments
     * @todo implement concerns injection
     */
    public function __call ($name, $arguments)
    {
        if (! method_exists($this->_object, $name)) {
            throw new \Rubedo\Exceptions\ServiceManager('The method ' . $name . ' doesn\'t exist');
        }
        
        //list of concerns
        $concernsArray = Rubedo\Interfaces\config::getConcerns();
        
        if (empty($concernsArray)) {
            $retour = call_user_func_array(array($this->_object,$name), $arguments);
        } else {
            $concernName = array_shift($concernsArray);
            $concern = new $concernName($concernsArray, $this->_currentOptions);
            $retour =  $concern->Process($this->_object, $name, $arguments);
        }
        
        return $retour;
    }
}