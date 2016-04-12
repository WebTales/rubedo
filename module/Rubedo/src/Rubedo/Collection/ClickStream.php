<?php
/**
 * Rubedo -- ECM solution
 * Copyright (c) 2014, WebTales (http://www.webtales.fr/).
 * All rights reserved.
 * licensing@webtales.fr
 *
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2014 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Collection;

use Rubedo\Interfaces\Collection\IClickStream;
use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\Json\Json;

/**
 * Service to handle ClickStream
 *
 * @author adobre
 * @category Rubedo
 * @package Rubedo
 */
class ClickStream extends AbstractCollection implements IClickStream
{

    public function __construct()
    {
        $this->_collectionName = 'ClickStream';
        parent::__construct();
    }
    protected $_indexes = array(
        array(
            'keys' => array(
                'fingerprint' => 1,
                'event' => 1,
                'sessionId' => 1,
            )
        ),array(
            'keys' => array(
                'fingerprint' => 1,
                'event' => 1
            )
        ),array(
            'keys' => array(
                'sessionId' => 1,
            )
        )
    );


    public function log($obj)
    {
      if (!isset($obj["fingerprint"],$obj["event"],$obj["eventId"])){
        return false;
      }
      $this->_dataService->directCreate($obj);
      return true;
    }

}
