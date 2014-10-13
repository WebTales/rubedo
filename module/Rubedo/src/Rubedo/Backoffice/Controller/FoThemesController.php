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
namespace Rubedo\Backoffice\Controller;

use WebTales\MongoFilters\Filter;
use Zend\Debug\Debug;
use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;
use Zend\View\Model\JsonModel;

/**
 * Controller providing the list of available Front Office Theme
 *
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *         
 */
class FoThemesController extends AbstractActionController
{
    /** @var string */
    protected $unzipDir;
    /** @var array */
    protected $virtualPath = array();
    /** @var array */
    protected $virtualDirectory;
    /** @var array */
    protected $templateDirectory;
    /** @var array */
    protected $cacheDirectory;
    /** @var \Rubedo\Collection\Directories */
    protected $directoriesService;
    /** @var \Rubedo\Collection\Dam */
    protected $damService;
    /** @var \Rubedo\Collection\Files */
    protected $filesService;

    function __construct()
    {
        $this->directoriesService = Manager::getService('Directories');
        $this->damService = Manager::getService('Dam');
        $this->filesService = Manager::getService('Files');
        $this->unzipDir = APPLICATION_PATH
            . DIRECTORY_SEPARATOR
            . 'cache'
            . DIRECTORY_SEPARATOR
            . 'zend'
            . DIRECTORY_SEPARATOR
            . 'themeArchive';
        $this->cleanDir($this->unzipDir);
    }
    /**
     * The default read Action
     *
     * Return the content of the collection, get filters from the request
     * params, get sort from request params
     */
    public function indexAction ()
    {
        $response = Manager::getService('FrontOfficeTemplates')->getAvailableThemes();
        return new JsonModel($response);
    }

    /**
     */
    public function getThemeInfosAction ()
    {
        $themeName = $this->params()->fromQuery('theme', 'default');
        $response = Manager::getService('FrontOfficeTemplates')->getThemeInfos($themeName);
        return new JsonModel($response);
    }

    public function getAvailableAction ()
    {
        $contextExist = Filter::factory('OperatorToValue')
            ->setName('context')
            ->setOperator('$exists')
            ->setValue(true);
        $foContext = Filter::factory('Value')
            ->setName('context')
            ->setValue('front');
        $foContextFilters = Filter::factory()
            ->addFilter($contextExist)
            ->addFilter($foContext);
        $response = Manager::getService('Themes')->getList($foContextFilters);
        $response['success'] = true;
        return new JsonModel($response);
    }

    public function importAction()
    {
        $zip = new \ZipArchive();
        $archive = $this->params()->fromFiles('archive');
        $result = array();
        if ($zip->open($archive['tmp_name'])) {
            $zip->extractTo($this->unzipDir);
            $zip->close();

            $it = new \RecursiveDirectoryIterator($this->unzipDir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::LEAVES_ONLY);

            $name = $this->params()->fromPost('name', 'default');
            $dirName = strtolower($name);
            $themeId = $this->createTemplateIfNotExist($name);
            $directoryToStore = $this->getTemplateDirectory($dirName, $themeId);
            foreach ($files as $file) {
                $directory = $this->getVirtualPathId($file->getRealPath(), $directoryToStore);
                $this->getOrCreateDam($file, $directory,$themeId);
            }

            $result['success'] = true;
        } else {
            $result['success'] = false;
            $result['message'] = 'Can\'t open zip';
        }
        return new JsonModel($result);
    }

    protected function getVirtualPathId($path, $rootDir) {
        $path = str_replace($this->unzipDir . DIRECTORY_SEPARATOR, '', $path);
        $pathArray = explode(DIRECTORY_SEPARATOR, $path);
        array_pop($pathArray);
        $currentPathArray = array();
        $lastOccurDir = $rootDir;
        foreach ($pathArray as $name) {
            $currentPathArray[] = $name;
            $currentPath = implode('.', $currentPathArray);
            if (!isset($this->cacheDirectory[$currentPath])) {
                $this->cacheDirectory[$currentPath] = $this->getDirectory($lastOccurDir['id'], $name);
            }
            $lastOccurDir = $this->cacheDirectory[$currentPath];
        }

        return $lastOccurDir;
    }

    protected function cleanDir ($dir) {
        try {
            $it = new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
        } catch (\UnexpectedValueException $e) {
            mkdir($dir);
        } catch (\Exception $e) {}
    }

    protected function getOrCreateDam(\SplFileInfo $file, $directory,$themeId)
    {

        $extension = substr(strchr($file->getFilename(), '.'), 1);
        switch ($extension) {
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'text/javascript';
                break;
            default:
                $mimeType = mime_content_type($file->getPathname());
                break;
        }
        $fileToCreate = array(
            'serverFilename' => $file->getPathname(),
            'text' => $file->getFilename(),
            'filename' => $file->getFilename(),
            'Content-Type' => isset($mimeType) ? $mimeType : 'text/plain',
            'mainFileType' => $this->filesService->getMainType($mimeType)
        );
        $mongoFile = $this->filesService->create($fileToCreate)['data'];

        $filters = Filter::factory('And');
        $filters
            ->addFilter(
                Filter::factory('Value')
                    ->setName('directory')
                    ->setValue($directory['id'])
            )
            ->addFilter(
                Filter::factory('Value')
                    ->setName('title')
                    ->setValue($file->getFileName())
            );
        $media = $this->damService->findOne($filters);
        if (empty($media)) {
            $media = array(
                'title' => $file->getFileName(),
                'themeId' => $themeId,
                'directory' => $directory['id'],
                'Content-Type' => $mimeType,
                'originalFileId' => $mongoFile['id'],
                'mainFileType' => 'Resource',
            );
            $this->damService->create($media, array(), false);
        } else {
            $media['originalFileId'] = $mongoFile['id'];
            $this->damService->update($media);
        }
    }

    protected function getTemplateDirectory($name, $themeId)
    {
        if (!isset($this->templateDirectory)) {
            $rootDirectory = $this->getRootDirectory();
            $this->templateDirectory = $this->getDirectory($rootDirectory['id'], $name, $themeId);
        }
        return $this->templateDirectory;
    }

    protected function createTemplateIfNotExist($name)
    {
        /** @var \Rubedo\Collection\Themes $themesCollection */
        $themesCollection = Manager::getService('Themes');
        $theme = $themesCollection->findByName($name);
        if (empty($theme)) {
            $createdTheme=$themesCollection->create(
                array(
                'context' => 'front',
                'text' => $name
                )
            );
            return ($createdTheme['data']["id"]);
        } else {
            return ($theme["id"]);
        }
    }

    protected function getRootDirectory()
    {
        if (!isset($this->virtualDirectory)) {
            $this->virtualDirectory = $this->getDirectory('root', 'theme');
            if ($this->virtualDirectory && !$this->virtualDirectory['expandable']) {
                $this->virtualDirectory['expandable'] = true;
                $this->directoriesService->update($this->virtualDirectory);
            }
        }
        return $this->virtualDirectory;
    }

    protected function getDirectory($parentId, $name, $themeId = null)
    {
        $filters = Filter::factory('And');
        $filters
            ->addFilter(
                Filter::factory('Value')
                    ->setName('parentId')
                    ->setValue($parentId)
            )
            ->addFilter(
                Filter::factory('Value')
                    ->setName('text')
                    ->setValue($name)
            );
        $directory = $this->directoriesService->findOne($filters);
        if (empty($directory)) {
            $toCreate = array(
                'filePlan' => 'default',
                'expandable' => true,
                'text' => $name,
                'parentId' => $parentId,
            );
            if (!empty($themeId)) {
                $toCreate['themeId'] = $themeId;
            }
            $directory = $this->directoriesService->create($toCreate)['data'];
        }
        return $directory;
    }
}
