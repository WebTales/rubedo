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
 * @version $Id:
 */
namespace Rubedo\Services;

use Rubedo, Rubedo\Interfaces\Services\IServicesManager;

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
    private $_object;

    /**
     * array of current service parameters
     *
     * @var array
     */
    private static $_servicesOptions;

    /**
     * current service name
     *
     * @var string
     */
    private $_serviceName;

    /**
     * Setter of services parameters, to init them from bootstrap
     *
     * @param array $options
     */
    public static function setOptions ($options)
    {
        if ('array' !== gettype($options)) {
            throw new \Exception('Services parameters should be an array');
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
     * private constructor : create manager object and nested service object
     *
     * @param string $serviceClassName Name of nested class
     * @param string $serviceName Name of the service
     */
    private function __construct ($serviceClassName, $serviceName)
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
    private function setServiceObj ($obj)
    {
        $this->_object = $obj;
        $this->_object->_service = $this;
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
            throw new \Exception('getService only accept string argument');
        }
        
        $serviceName = ucfirst($serviceName);
        
        $serviceClassName = self::resolveName($serviceName);
        
        if (! in_array('I' . $serviceName, class_implements($serviceClassName))) {
            throw new \Exception($serviceClassName . ' don\'t implement I' . $serviceName);
        }
        
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
    private static function resolveName ($serviceName)
    {
        $options = self::$_servicesOptions;
        
        return str_replace('_', '\\', $serviceName);
        /*
         * if (isset($options[$serviceName]['class'])) { } else { throw new
         * \Exception('Classe name should be '); } if
         * (isset($options[$serviceName]['bouchon']) && 1 ==
         * $options[$serviceName]['bouchon'] && class_exists(
         * 'Application_Model_Services_Bouchon_' . $serviceName)) {
         * error_log($serviceName . ' : utilisation du bouchon'); return
         * 'Application_Model_Services_Bouchon_' . $serviceName; } elseif
         * (class_exists('Application_Model_Services_Implementation_' .
         * $serviceName)) { return
         * 'Application_Model_Services_Implementation_' . $serviceName; } else
         * { throw new \Exception('Application_Model_Services_Implementation_'
         * . $serviceName . ' n\'existe pas'); }
         */
    }

    /**
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
            throw new \Exception('The method ' . $name . ' doesn\'t exist');
        }
        
        // liste des injecteurs pouvant s'exécuter
        /*
         * $injecteurArray = array(
         * 'Application_Model_Services_Injecteur_Log',
         * 'Application_Model_Services_Injecteur_Acl',
         * 'Application_Model_Services_Injecteur_Cache',
         * 'Application_Model_Services_Injecteur_FilterOutput',
         * 'Application_Model_Services_Injecteur_Transaction'); // on dépile
         * le premier injecteur puis on instancie le pipeline $injecteurName =
         * array_shift($injecteurArray); $injecteur = new
         * $injecteurName($injecteurArray, $this->_currentOptions); $retour =
         * $injecteur->Process($this->_object, $name, $arguments);
         */
        
        $retour = call_user_func_array(array($this->_object,$name), $this->_object);
        return $retour;
    }
}