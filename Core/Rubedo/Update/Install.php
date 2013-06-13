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

/**
 * Methods for install tool
 *
 * @author jbourdin
 *        
 */
class Install
{

    public static function doInsertContents ()
    {
        $success = true;
        $contentPath = APPLICATION_PATH . '/../data/default/';
        $contentIterator = new \DirectoryIterator($contentPath);
        foreach ($contentIterator as $directory) {
            if ($directory->isDot() || ! $directory->isDir()) {
                continue;
            }
            if (in_array($directory->getFilename(), array(
                'groups',
                'site'
            ))) {
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
                    $item = \Zend_Json::decode($itemJson);
                    try {
                        if (! Manager::getService($collection)->findOne(Filter::factory('Value')->setName('defaultId')
                            ->setValue($item['defaultId']))) {
                            $result = Manager::getService($collection)->create($item);
                        } else {
                            $result['success'] = true;
                        }
                    } catch (\Rubedo\Exceptions\User $exception) {
                        $result['success'] = true;
                    }
                    
                    $success = $result['success'] && $success;
                }
            }
        }
        return $success;
    }

    public static function setDbVersion ($version)
    {
        Manager::getService('RubedoVersion')->setDbVersion($version);
    }
}