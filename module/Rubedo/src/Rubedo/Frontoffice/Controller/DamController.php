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
namespace Rubedo\Frontoffice\Controller;

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
class DamController extends AbstractActionController
{

    public function indexAction ()
    {
        $mediaId = $this->params()->fromQuery('media-id');
        
        if (! $mediaId) {
            throw new \Rubedo\Exceptions\User('no id given', "Exception7");
        }
        $media = Manager::getService('Dam')->findById($mediaId);
        if (! $media) {
            throw new \Rubedo\Exceptions\NotFound('no media found', "Exception8");
        }
        
        $version = $this->params()->fromQuery('version', $media['id']);
        
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new \Rubedo\Exceptions\Server('unknown media type', "Exception9");
        }
        if (isset($mediaType['mainFileType']) && $mediaType['mainFileType'] == 'Image') {
            $queryString = $this->getRequest()->getQuery();
            $params = array(
                'file-id' => $media['originalFileId'],
                'attachment' => $this->params()->fromQuery('attachment', null),
                'version' => $version
            );
            foreach ($params as $key => $value) {
                $queryString->set($key, $value);
            }
            
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
                'action' => 'index'
            ));
        } else {
            $queryString = $this->getRequest()->getQuery();
            $params = array(
                'file-id' => $media['originalFileId'],
                'attachment' => $this->params()->fromQuery('attachment', null),
                'version' => $version
            );
            foreach ($params as $key => $value) {
                $queryString->set($key, $value);
            }
            
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\File', array(
                'action' => 'index'
            ));
        }
    }

    public function rewriteAction ()
    {
        $mediaId = $this->params('mediaId');
        
        if (! $mediaId) {
            throw new \Rubedo\Exceptions\User('no id given', "Exception7");
        }
        $media = Manager::getService('Dam')->findById($mediaId);
        if (! $media) {
            throw new \Rubedo\Exceptions\NotFound('no media found', "Exception8");
        }
        
        $version = $this->params('version', $media['id']);
        
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new \Rubedo\Exceptions\Server('unknown media type', "Exception9");
        }
        if (isset($mediaType['mainFileType']) && $mediaType['mainFileType'] == 'Image') {
            $queryString = $this->getRequest()->getQuery();
            $params = array(
                'file-id' => $media['originalFileId'],
                'attachment' => $this->params()->fromQuery('attachment', null),
                'version' => $version
            );
            foreach ($params as $key => $value) {
                $queryString->set($key, $value);
            }
            
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
                'action' => 'index'
            ));
        } else {
            $queryString = $this->getRequest()->getQuery();
            $params = array(
                'file-id' => $media['originalFileId'],
                'attachment' => $this->params()->fromQuery('attachment', null),
                'version' => $version
            );
            foreach ($params as $key => $value) {
                $queryString->set($key, $value);
            }
            
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\File', array(
                'action' => 'index'
            ));
        }
    }

    public function getThumbnailAction ()
    {
        $mediaId = $this->params()->fromQuery('media-id', null);
        if (! $mediaId) {
            throw new \Rubedo\Exceptions\User('no id given', "Exception7");
        }
        $media = Manager::getService('Dam')->findById($mediaId);
        if (! $media) {
            throw new \Rubedo\Exceptions\NotFound('no media found', "Exception8");
        }
        $version = $this->params()->fromQuery('version', $media['id']);
        
        $mediaType = Manager::getService('DamTypes')->findById($media['typeId']);
        if (! $mediaType) {
            throw new \Rubedo\Exceptions\Server('unknown media type', "Exception9");
        }
        if ($mediaType['mainFileType'] == 'Image') {
            $queryString = $this->getRequest()->getQuery();
            $params = array(
                'file-id' => $media['originalFileId'],
                'version' => $version
            );
            foreach ($params as $key => $value) {
                $queryString->set($key, $value);
            }
            
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\Image', array(
                'action' => 'get-thumbnail'
            ));
        } else {
            $queryString = $this->getRequest()->getQuery();
            $queryString->set('file-id', $media['originalFileId']);
            $queryString->set('version', $version);
            return $this->forward()->dispatch('Rubedo\\Frontoffice\\Controller\\File', array(
                'action' => 'get-thumbnail'
            ));
        }
    }
}
