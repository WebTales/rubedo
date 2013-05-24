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
 * 
 *
 * @category   Rubedo
 * @package    Rubedo
 * @copyright  Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license    http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Version;

use Rubedo\Services\Manager;
/**
 * Class to store and retrieve the version of Rubedo.
 *
 * Design the same way than Zend Framework 2 Version class
 */
final class Version
{

    /**
     * Zend Framework version identification - see compareVersion()
     */
    const VERSION = '1.2.0dev';

    /**
     * Github Service Identifier for version information is retreived from
     */
    const VERSION_SERVICE_GITHUB = 'GITHUB';

    /**
     * Zend (framework.zend.com) Service Identifier for version information is retreived from
     */
    const VERSION_SERVICE_ZEND = 'ZEND';

    /**
     * The latest stable version Zend Framework available
     *
     * @var string
     */
    protected static $latestVersion;

    /**
     * Compare the specified Zend Framework version string $version
     * with the current Zend\Version\Version::VERSION of Zend Framework.
     *
     * @param string $version
     *            A version string (e.g. "0.7.1").
     * @return int -1 if the $version is older,
     *         0 if they are the same,
     *         and +1 if $version is newer.
     *        
     */
    public static function compareVersion ($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);
        return version_compare($version, strtolower(self::VERSION));
    }

    /**
     * Return the version of the current instance of Rubedo
     * 
     * @return string
     */
    public static function getVersion ()
    {
        return self::VERSION;
    }

    /**
     * Fetches the version of the latest stable release.
     *
     * By Default, this uses the GitHub API (v3) and only returns refs that begin with
     * 'tags/release-'. Because GitHub returns the refs in alphabetical order,
     * we need to reduce the array to a single value, comparing the version
     * numbers with version_compare().
     *
     * If $service is set to VERSION_SERVICE_ZEND this will fall back to calling the
     * classic style of version retreival.
     *
     *
     * @see http://developer.github.com/v3/git/refs/#get-all-references
     * @link https://api.github.com/repos/zendframework/zf2/git/refs/tags/release-
     * @link http://framework.zend.com/api/zf-version?v=2
     * @param string $service
     *            Version Service with which to retrieve the version
     * @return string
     */
    public static function getLatest ($service = self::VERSION_SERVICE_GITHUB)
    {
        if (null === static::$latestVersion) {
            static::$latestVersion = 'not available';
            if ($service == self::VERSION_SERVICE_GITHUB) {
                $url = 'https://api.github.com/repos/webtales/rubedo/git/refs/tags';
                
                $ch = curl_init();
                
                // Configuration de l'URL et d'autres options
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/27.0.1453.93 Safari/537.36");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                
                $result = curl_exec($ch);
                curl_close($ch);
                
                if ($result === false) {
                    return null;
                }
                $apiResponse = \Zend_Json::decode($result);
                
                // Simplify the API response into a simple array of version numbers
                $tags = array_map(function  ($tag)
                {
                    return substr($tag['ref'], 10); // Reliable because we're filtering on 'refs/tags/release-'
                }, $apiResponse);
                
                // Fetch the latest version number from the array
                static::$latestVersion = array_reduce($tags, function  ($a, $b)
                {
                    return version_compare($a, $b, '>') ? $a : $b;
                });
            }
        }
        
        return static::$latestVersion;
    }

    /**
     * Returns true if the running version of Zend Framework is
     * the latest (or newer??) than the latest tag on GitHub,
     * which is returned by static::getLatest().
     *
     * @return bool
     */
    public static function isLatest ()
    {
        return static::compareVersion(static::getLatest()) < 1;
    }

    public static function getComponentsVersion ()
    {
        $componentsArray = array();
        $componentsArray['phpComponents'] = array('MongoDriver'=> \MongoClient::VERSION);
        
        
        
        if (is_file(APPLICATION_PATH . '/../composer.lock')) {
            $phpComponentsArray = \Zend_Json::decode(file_get_contents(APPLICATION_PATH . '/../composer.lock'));
            foreach ($phpComponentsArray['packages'] as $package) {
                if ($package['name'] == 'bombayworks/zendframework1') {
                    continue;
                }
                $componentsArray['phpComponents'][$package['name']] = $package['version'];
            }
        }
        
        
        $componentsArray['frontComponents'] = array();
        if (is_file(APPLICATION_PATH . '/../public/composer.lock')) {
            $phpComponentsArray = \Zend_Json::decode(file_get_contents(APPLICATION_PATH . '/../public/composer.lock'));
            foreach ($phpComponentsArray['packages'] as $package) {
                $componentsArray['frontComponents'][$package['name']] = $package['version'];
            }
        }
        return $componentsArray;
    }
    
    public static function getMongoServerVersion(){
       return Manager::getService('MongoDataAccess')->getMongoServerVersion();
    }
    
    public static function getESServerVersion(){
        $esService = Manager::getService('ElasticDataIndex');
        $esService->init();
        return $esService->getVersion();
    }
    
}