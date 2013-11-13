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
namespace Rubedo\Update;

use Rubedo\Services\Manager;
use WebTales\MongoFilters\Filter;
use Zend\Json\Json;
/**
 * Methods
 * for
 * update
 * tool
 *
 * @author jbourdin
 *        
 */
class Update020000 extends Update
{

    protected static $toVersion = '2.1.0';


    public static function doCreateDefaultUserTypes ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/data/default/';
        $contentIterator = new \DirectoryIterator($contentPath);
        foreach ($contentIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            if ($directory->getFilename()!="UserTypes"){
                continue;
            }
            $collection = ucfirst($directory->getFilename());
            $itemsJson = new \DirectoryIterator($contentPath . '/' . $directory->getFilename());
            foreach ($itemsJson as $file) {
                if ($file->isDot() || $file->isDir()) {
                    continue;
                }
                if ($file->getExtension() == 'json') {
                    $itemJson = file_get_contents($file->getPathname());
                    $item = Json::decode($itemJson,Json::TYPE_ARRAY);
                    try {
                        switch ($collection) {
                            case 'UserTypes':
                                $property = 'type';
                                break;
                            default:
                                $property = 'text';
                                break;
                        }
                        $filter = Filter::factory('Value')->setName($property)->setValue($item[$property]);
                        $result = Manager::getService($collection)->customUpdate(array(
                            '$set' => array(
                                'defaultId' => $item['defaultId']
                            )
                        ), $filter);
                    } catch (\Rubedo\Exceptions\User $exception) {
                        $result['success'] = true;
                    }

                    $success = $result['success'] && $success;
                }
            }
        }

        return $success;
    }
    /**
     * do
     * the
     * upgrade
     *
     * @return boolean
     */
    public static function upgrade ()
    {

        // create default user types
        static::doCreateDefaultUserTypes();

        return true;
    }

    
}