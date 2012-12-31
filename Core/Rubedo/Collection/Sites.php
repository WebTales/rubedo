<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2012, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license. 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\ISites;

/**
 * Service to handle Sites
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Sites extends AbstractCollection implements ISites
{

    protected static $_overrideSiteName = array();

    protected static $_overrideSiteNameReverse = array();

    public static function setOverride (array $array)
    {
        foreach ($array as $key => $value) {
            
            $newArray[str_replace('_', '.', $key)] = str_replace('_', '.', $value);
        }
        self::$_overrideSiteName = $newArray;
        self::$_overrideSiteNameReverse = array_flip($newArray);
    }

    public function __construct ()
    {
        $this->_collectionName = 'Sites';
        parent::__construct();
    }

    public function getHost ($site)
    {
        if (is_string($site)) {
            $site = $this->findById($site);
        }
        $label = $site['text'];
        if (isset(self::$_overrideSiteName[$label])) {
            $label = self::$_overrideSiteName[$label];
        }
        return $label;
    }

    public function findByHost ($host)
    {
        if (isset(self::$_overrideSiteNameReverse[$host])) {
            $host = self::$_overrideSiteNameReverse[$host];
        }
        
        $site = $this->findByName($host);
        if ($site === null) {
            $site = $this->_dataService->findOne(array(
                'alias' => $host
            ));
        }
        return $site;
    }
}
