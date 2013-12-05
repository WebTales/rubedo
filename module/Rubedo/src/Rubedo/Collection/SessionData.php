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
namespace Rubedo\Collection;

use WebTales\MongoFilters\Filter;

/**
 * Service to read session data without using session handler
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class SessionData extends AbstractCollection
{

    /**
     * Name of the session
     *
     * @var string
     */
    protected static $sessionName = 'rubedo';

    public function __construct()
    {
        $this->_collectionName = 'sessions';
        parent::__construct();
    }

    public function findById($token)
    {
        if (empty($token)) {
            return null;
        }
        $filter = Filter::factory('Value')->setName('_id')->setValue($token);
        return $this->findOne($filter);
    }

    public function decode($session_data)
    {
        $return_data = array();
        $offset = 0;
        while ($offset < strlen($session_data)) {
            if (!strstr(substr($session_data, $offset), "|")) {
                throw new \Exception("invalid data, remaining: " . substr($session_data, $offset));
            }
            $pos = strpos($session_data, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($session_data, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($session_data, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    /**
     *
     * @return the $sessionName
     */
    public function getSessionName()
    {
        return SessionData::$sessionName;
    }

    /**
     *
     * @param field_type $sessionName
     */
    public static function setSessionName($sessionName)
    {
        SessionData::$sessionName = $sessionName;
    }
}
