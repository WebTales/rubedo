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
namespace Rubedo\Frontoffice\Controller;

use WebTales\MongoFilters\Filter;
use Zend\Json\Json;
use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;

/**
 * Controller providing access to images in gridFS
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of
 * them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class ThemeController extends AbstractActionController
{

    function indexAction()
    {
        $theme = $this->params()->fromRoute('theme');
        $filePath = $this->params()->fromRoute('filepath');
        $config = manager::getService('Config');
        /** @var \Rubedo\Collection\Directories $directoriesCollection */
        $directoriesCollection = Manager::getService('Directories');
        $filters = Filter::factory('And');
        $filters
            ->addFilter(
                Filter::factory('Value')
                    ->setName('parentId')
                    ->setValue('root')
            )
            ->addFilter(
                Filter::factory('Value')
                    ->setName('text')
                    ->setValue('theme')
            );
        $rootDirectory = $directoriesCollection->findOne($filters);
        $hasFileInDatabase = false;
        if (!empty($rootDirectory)) {
            $directories = $directoriesCollection->fetchAndSortAllChildren($rootDirectory['id']);
            $directoryExploded = explode('/', $theme . '/' . $filePath);
            $file = array_pop($directoryExploded);
            $dirWhereSearch = $this->recursivePathExist($directories, $directoryExploded);
            if (!empty($dirWhereSearch)) {
                /** @var \Rubedo\Collection\Dam $damCollection */
                $damCollection = Manager::getService('Dam');
                $media = $damCollection->findOne(
                    Filter::factory('And')
                        ->addFilter(
                            Filter::factory('Value')
                                ->setValue($file)
                                ->setName('title')
                        )
                        ->addFilter(
                            Filter::factory('Value')
                                ->setValue($dirWhereSearch)
                                ->setName('directory')
                        )
                );
                if (!empty($media)) {
                    $fileService = Manager::getService('Files');
                    $mimeType = $media['Content-Type'];
                    $gridFSFile = $fileService->findById($media['originalFileId']);
                    if ($gridFSFile instanceof \MongoGridFSFile) {
                        $hasFileInDatabase = true;
                    }
                }
            }
        }

        if (!$hasFileInDatabase) {

            $consolidatedFilePath = Manager::getService('FrontOfficeTemplates')->getFilePath($theme, $filePath);

            if (!$consolidatedFilePath) {
                $consolidatedFilePath = Manager::getService('FrontOfficeTemplates')->getFilePath("default", $filePath);
            }

            if ($consolidatedFilePath||$filePath=="css/rubedo-all.css"||$filePath=="js/rubedo-all.js"||$filePath=="js/rubedo-all-blocks.js") {
                if ($filePath=="css/rubedo-all.css"&&!$consolidatedFilePath){
                    $extension="css";
                    $mimeType = 'text/css';
                } elseif ($filePath=="js/rubedo-all.js"&&!$consolidatedFilePath){
                    $extension="js";
                    $mimeType = 'application/javascript';
                } elseif ($filePath=="js/rubedo-all-blocks.js"&&!$consolidatedFilePath){
                    $extension="js";
                    $mimeType = 'application/javascript';
                }else {
                    $extension = pathinfo($consolidatedFilePath, PATHINFO_EXTENSION);

                    switch ($extension) {
                        case 'php':
                            throw new \Rubedo\Exceptions\NotFound('File does not exist');
                            break;
                        case 'js':
                            $mimeType = 'application/javascript';
                            break;
                        case 'css':
                            $mimeType = 'text/css';
                            break;
                        case 'html':
                            $mimeType = 'text/html';
                            break;
                        case 'json':
                            $mimeType = 'application/json';
                            break;
                        default:
                            if (class_exists('finfo')) {
                                $finfo = new \finfo(FILEINFO_MIME);
                                $mimeType = $finfo->file($consolidatedFilePath);
                            }
                            break;
                    }
                }
                $publicThemePath = APPLICATION_PATH . '/public/theme';
                $composedPath = $publicThemePath . '/' . $theme;
                try {
                    if (!file_exists($composedPath)) {
                        mkdir($composedPath, 0777);
                    }
                } catch(\Exception $e){
                    \Monolog\Handler\error_log($e->getMessage());
                }

                $composedPath = $composedPath . '/' . dirname($filePath);
                try {
                    if (!file_exists($composedPath)) {
                        mkdir($composedPath, 0777, true);
                    }
                } catch(\Exception $e){
                    \Monolog\Handler\error_log($e->getMessage());
                }
                $targetPath = $publicThemePath . '/' . $theme . '/' . $filePath;

                if ($filePath=="css/rubedo-all.css"&&!$consolidatedFilePath){
                    $content=$this->getAllCss($theme,$config);
                } elseif ($filePath=="js/rubedo-all.js"&&!$consolidatedFilePath){
                    $content=$this->getAllJs($theme,$config);
                } elseif ($filePath=="js/rubedo-all-blocks.js"&&!$consolidatedFilePath){
                    $content=$this->getAllBlocks($theme,$config,$this->params()->fromQuery());
                } else {
                    $content = file_get_contents($consolidatedFilePath);
                }


                if (isset($config['rubedo_config']['minify']) && $config['rubedo_config']['minify'] == "1") {
                    if ($mimeType == 'text/css') {
                        $content = \Minify_CSS::minify($content, array(
                            'preserveComments' => false
                        ));
                    } elseif ($mimeType == 'application/javascript') {
                        $content = \JSMin::minify($content);
                    }
                }
            }

        }
        $response = new \Zend\Http\Response\Stream();
        $headers = array(
            'Pragma' => 'Public',
        );
        if (isset($mimeType)) {
            $headers['Content-type'] = $mimeType;
        }
        if (isset($targetPath)&&(((isset($config['rubedo_config']['cachePage']) && $config['rubedo_config']['cachePage'] == "1"))||$filePath=="css/rubedo-all.css"||$filePath=="js/rubedo-all.js"||$filePath=="js/rubedo-all-blocks.js") && file_put_contents($targetPath, $content)) {
            $stream = fopen($targetPath, 'r');
        } elseif ($hasFileInDatabase) {
            $stream = $gridFSFile->getResource();
            $filelength = $gridFSFile->getSize();

            $headers = array_replace($headers, array(
                'Content-Length' => $filelength,
                'Content-Range' => "bytes 0-/$filelength",
            ));
            fseek($stream, 0);
            $response->setStream($stream);
        } elseif ($consolidatedFilePath) {
            $stream = fopen($consolidatedFilePath, 'r');
        } else {
            throw new \Rubedo\Exceptions\NotFound('File does not exist');
        }

        if (isset($config['rubedo_config']['cachePage']) && $config['rubedo_config']['cachePage'] == "1") {
            $headers['Cache-Control'] = 'public, max-age=' . 7 * 24 * 3600;
            $headers['Expires'] = date(DATE_RFC822, strtotime("7 day"));
        }

        $fileContent = stream_get_contents($stream);
        rewind($stream);
        if($fileContent) {
            $etag = hash("sha256", $fileContent);

            $headers['Etag'] = $etag;

            $browserEtag = $this->getRequest()->getHeader("If-None-Match");

            if($browserEtag && $browserEtag->getFieldValue() === $etag) {
                $response->setStatusCode(304);
            } else {
                $response->setStream($stream);
            }
        }

        $response->getHeaders()->addHeaders($headers);

        return $response;
    }

    protected function recursivePathExist($directories, $directoryExploded)
    {
        $currentDirName = array_shift($directoryExploded);
        foreach ($directories as $directory) {
            if ($directory['text'] === $currentDirName) {
                if (empty($directoryExploded)) {
                    return $directory['id'];
                } elseif (isset($directory['children'])) {
                    return $this->recursivePathExist($directory['children'], $directoryExploded);
                }
                return false;
            }
        }
        return false;
    }

    protected function getAllCss($theme,$config){
        $concatResult="";
        $cssDependencyArray=["css/rubedo.css","libraryOverrides/chosen.css"];
        $themesService = Manager::getService('Themes');
        $themeObj = $themesService->findByName($theme);
        if ($themeObj) {
            /** @var \Rubedo\Collection\DAM $DAMService */
            $DAMService = Manager::getService('DAM');
            $filters = Filter::factory()
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('loadOnLaunch')
                        ->setValue(true)
                )
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('themeId')
                        ->setValue($themeObj['id'])
                )
                ->addFilter(
                    Filter::factory('OperatorToValue')
                        ->setName('title')
                        ->setOperator('$regex')
                        ->setValue('.+(css|js)$')
                );
            $themeFilesToLoad = $DAMService->getList($filters)['data'];
            foreach ($themeFilesToLoad as &$fileToLoad) {
                $themeFile =implode('/', $this->discoverDirNames(array(), $fileToLoad['directory'],$theme)). '/' . $fileToLoad['title'];
                $extension = substr(strrchr($themeFile, '.'), 1);
                if ($extension == 'css') {
                    $cssDependencyArray[] = $themeFile;
                }
            }
        }
        $themeName = strtolower($theme);
        if (isset($config['templates']['themes'][$themeName])) {
            $themeConf = $config['templates']['themes'][$themeName];

            if (isset($themeConf['css'])) {
                foreach ($themeConf['css'] as $css) {
                    $cssDependencyArray[] =  $css;
                }
            }

        }
        foreach($cssDependencyArray as $cssDependency){
            $redirectedResult=$this->forward()->dispatch("Rubedo\\Frontoffice\\Controller\\Theme", array(
                'action' => 'index',
                'theme' => $theme,
                'filepath' => $cssDependency,
            ));
            $concatResult=$concatResult." ".$redirectedResult->getBody();
        }
        return($concatResult);
    }

    protected function getAllJs($theme,$config){
        $concatResult="";
        $themeName = strtolower($theme);
        $themesService = Manager::getService('Themes');
        $themeObj = $themesService->findByName($theme);
        $jsDependencyArray=[
            "libraryOverrides/lrInfiniteScroll.js",
            "libraryOverrides/angular-google-map.js",
            "libraryOverrides/chosen.jquery.js",
            "lib/toaster/jquery.toaster.js",
            "lib/angularStrap/ngStrap.js",
        ];
        //ext dependencies modules
        if (isset($config['templates']['themes'][$themeName])) {
            $themeConf = $config['templates']['themes'][$themeName];
            if (isset($themeConf['angularModules'])) {
                foreach ($themeConf['angularModules'] as $angularModule => $angularModulePath) {
                    $jsDependencyArray[] = $angularModulePath;
                }
            }
        }
        $jsDependencyArray[]="src/modules/rubedoDataAccess/rubedoDataAccess.js";
        $jsDependencyArray[]="src/modules/rubedoFields/rubedoFields.js";
        $jsDependencyArray[]="src/modules/rubedoBlocks/rubedoBlocks.js";
        $jsDependencyArray[]="src/modules/rubedo/rubedo.js";
        //ext dependencies js
        if ($themeObj) {
            /** @var \Rubedo\Collection\DAM $DAMService */
            $DAMService = Manager::getService('DAM');
            $filters = Filter::factory()
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('loadOnLaunch')
                        ->setValue(true)
                )
                ->addFilter(
                    Filter::factory('Value')
                        ->setName('themeId')
                        ->setValue($themeObj['id'])
                )
                ->addFilter(
                    Filter::factory('OperatorToValue')
                        ->setName('title')
                        ->setOperator('$regex')
                        ->setValue('.+(css|js)$')
                );
            $themeFilesToLoad = $DAMService->getList($filters)['data'];
            foreach ($themeFilesToLoad as &$fileToLoad) {
                $themeFile =implode('/', $this->discoverDirNames(array(), $fileToLoad['directory'],$theme)). '/' . $fileToLoad['title'];
                $extension = substr(strrchr($themeFile, '.'), 1);
                if ($extension == 'js') {
                    $jsDependencyArray[] = $themeFile;
                }
            }
        }
        if (isset($config['templates']['themes'][$themeName])) {
            $themeConf = $config['templates']['themes'][$themeName];
            if (isset($themeConf['js'])) {
                foreach ($themeConf['js'] as $js) {
                    $jsDependencyArray[] =  $js;
                }
            }
        }
        foreach($jsDependencyArray as $jsDependency){
            $redirectedResult=$this->forward()->dispatch("Rubedo\\Frontoffice\\Controller\\Theme", array(
                'action' => 'index',
                'theme' => $theme,
                'filepath' => $jsDependency,
            ));
            $concatResult=$concatResult." ".$redirectedResult->getBody();
        }
        return($concatResult);
    }

    protected function getAllBlocks($theme,$config,$params){
        $concatResult="";

        $jsDependencyArray=[];
        if (isset($params["blockconfig"])&&is_string($params["blockconfig"])){
            $jsDependencyArray=Json::decode($params["blockconfig"],Json::TYPE_ARRAY);
        }
        foreach($jsDependencyArray as $jsDependency){
            if(isset($jsDependency[0])&&$jsDependency[0]=="/"){
                $jsDependency=substr($jsDependency,1);
            }
            $redirectedResult=$this->forward()->dispatch("Rubedo\\Frontoffice\\Controller\\Theme", array(
                'action' => 'index',
                'theme' => $theme,
                'filepath' => $jsDependency,
            ));
            $concatResult=$concatResult." ".$redirectedResult->getBody();
        }
        return($concatResult);
    }

    function discoverDirNames($dirs, $nextDir,$theme)
    {
        if ($nextDir === 'root') {
            return $dirs;
        }
        /** @var \Rubedo\Collection\Directories $dirService */
        $dirService = Manager::getService('Directories');
        $directory = $dirService->findById($nextDir);
        if ($directory&&$directory['text']&&$directory['text']!="theme"&&$directory['text']!=$theme) {
            array_unshift($dirs, $directory['text']);
        }
        return $this->discoverDirNames($dirs, $directory['parentId'],$theme);
    }
}
