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
/**
 * Methods for update database
 *
 * @author jbourdin
 *        
 */
abstract class Update extends Install
{
    protected static $toVersion;
    
    public static function run(){
        if(static::upgrade()){
            static::setDbVersion(static::$toVersion);
        }
        return true;
    }
    
    public static function update(){
        $rubedoDbVersionService = Manager::getService('RubedoVersion');
        while(! $rubedoDbVersionService->isDbUpToDate()) {
            $currentDbVersion = $rubedoDbVersionService->getDbVersion();
            $classNameArray = explode('.',$currentDbVersion);
            $classNameSuffix = call_user_func_array('sprintf',array_merge(array('%02d%02d%02d'), $classNameArray));
        
            $updateClassName = '\\Rubedo\\Update\\'.'Update' . $classNameSuffix;
            if (@class_exists($updateClassName)) {
                $updateClassName::run();
            }else{
                throw new \Rubedo\Exceptions\Server('Class %1$s does not exists.', "Exception90", $updateClassName);
            }
        }
        return $rubedoDbVersionService->getDbVersion();
    }
    
    public static function upgrade(){
        throw new \Rubedo\Exceptions\Server('Upgrade method for class %1$s does not exists.', "Exception91", get_called_class());
    }
    
}