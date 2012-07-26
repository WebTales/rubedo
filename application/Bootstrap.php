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

/**
 * Application initialization class
 *
 * @author jbourdin
 *        
 */
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    /**
     * Init the DB info by setting static values in the DB class
     */
    protected function _initMongoDataStream ()
    {
        $options = $this->getOption('datastream');
        if (isset($options)) {
            $connectionString = 'mongodb://';
            if (! empty($options['mongo']['login'])) {
                $connectionString .= $options['mongo']['login'];
                $connectionString .= ':' . $options['mongo']['password'] . '@';
            }
            $connectionString .= $options['mongo']['server'];
            Rubedo\Mongo\DataAccess::setDefaultMongo($connectionString);
            
            Rubedo\Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
        }
    }
}

