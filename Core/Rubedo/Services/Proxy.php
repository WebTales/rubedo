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

use Rubedo, Rubedo\Interfaces, Rubedo\Interfaces\Services\IServicesProxy;

/**
 * Service Proxy
 *
 * Proxy to actual services
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Proxy implements IServicesProxy
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
     * Getter of the current service parameters or the specified parameter
     *
     * @param string $name optionnal parameter name
     * @return mixed value or array of valuefor asked parameter
     */
    public function getCurrentOptions($name = null)
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
    public function getServiceName()
    {
        return $this->_serviceName;
    }

    /**
     * protected constructor : create manager object and nested service object
     *
     * @param string $serviceClassName Name of nested class
     * @param string $serviceName Name of the service
	 * @param object $serviceObject Override the service object
     */
    public function __construct($serviceClassName, $serviceName, $serviceObject = null)
    {
    	if($serviceObject===null){
        $this->setServiceObj(new $serviceClassName());
		}elseif($serviceObject instanceof $serviceClassName){
			$this->setServiceObj($serviceObject);
		}else{
			throw new \Rubedo\Exceptions\ServiceManager("Override Object not an instance of service Classe Name", 1);
		}
        $this->_serviceName = $serviceName;
		
		$options = Manager::getOptions();

        if (isset($options[$serviceName])) {
            $this->_currentOptions = $options[$serviceName];
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
    public function setServiceObj($obj)
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
    public function getServiceObj()
    {
        return $this->_object;
    }

    /**
     * Call : magic method invoke when calling a none existing manager method,
     * proxy to the service object
     *
     * @param string $name service method name
     * @param array $arguments service method array of arguments
     * @todo implement concerns injection
     */
    public function __call($name, $arguments)
    {
        if (!method_exists($this->_object, $name)) {
            throw new \Rubedo\Exceptions\ServiceManager('The method ' . $name . ' doesn\'t exist');
        }

        //list of concerns
        $concernsArray = Rubedo\Interfaces\config::getConcerns();

        if (empty($concernsArray)) {
            $retour = call_user_func_array(array($this->_object, $name), $arguments);
        } else {
            $concernName = array_shift($concernsArray);
            $concern = new $concernName($concernsArray, $this->_currentOptions);
            $retour = $concern->Process($this->_object, $name, $arguments);
        }

        return $retour;
    }

}
