<?php
/**
 * Rubedo
 *
 * LICENSE
 *
 * yet to be written
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2012 WebTales (http://www.webtales.fr)
 * @license yet to be written
 * @version $Id$
 */

use Rubedo\Mongo\DataAccess, Rubedo\Mongo, Rubedo\Services;

/**
 * Controller providing access to images in gridFS
 *
 * Receveive Ajax Calls with needed ressources, send true or false for each of them
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 *
 */
class ImageController extends Zend_Controller_Action
{

    function indexAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $fileId = $this->getRequest()->getParam('file-id');

        if (isset($fileId)) {
            $fileService = Rubedo\Services\Manager::getService('Images');
            $obj = $fileService->findById($fileId);
			if(! $obj instanceof MongoGridFSFile){
				throw new Zend_Controller_Exception("No Image Found", 1);
			}
            $image = $obj->getBytes();

            $this->getResponse()->clearBody();
            $this->getResponse()->setHeader('Content-Type', 'image/jpeg');
            $this->getResponse()->setBody($image);

        } else {
            throw new Zend_Controller_Exception("No Id Given", 1);

        }

    }

}
