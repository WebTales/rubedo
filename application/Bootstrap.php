<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initMongoDataStream(){
        $options = $this->getOption('datastream');
        if (isset($options))
        {
            //Application_Model_Services_Manager::setOptions($options);
            $mongoConnectionString = 'mongodb://';
            if(!empty($options['mongo']['login'])){
                $mongoConnectionString .= $options['mongo']['login'];
                $mongoConnectionString .= ':'.$options['mongo']['password'].'@';
            }
            $mongoConnectionString .= $options['mongo']['server'];
            Rubedo\Mongo\DataAccess::setDefaultMongo($mongoConnectionString);
            
            Rubedo\Mongo\DataAccess::setDefaultDb($options['mongo']['db']);
        }
    }

}

