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

use Zend\Mvc\Controller\AbstractActionController;
use Rubedo\Services\Manager;

class ExtensionPathController extends AbstractActionController
{

    function indexAction()
    {
        $name = $this->params()->fromRoute('name');
        $filePath = $this->params()->fromRoute('filepath');
        $config = Manager::getService('Application')->getConfig();
        if (
        !isset (
            $config['extension_paths'],
            $config['extension_paths'][$name],
            $config['extension_paths'][$name]['path']
        )
        ) {
            throw new \Rubedo\Exceptions\NotFound('File does not exist');
        }
        $extension = &$config['extension_paths'][$name];
        $path = &$extension['path'];
        $file = $path . '/' . $filePath;
        if (!file_exists($file)) {
            throw new \Rubedo\Exceptions\NotFound('File does not exist');
        }
        switch (substr(strrchr($file, '.'), 1)) {
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'text/javascript';
                break;
            default:
                $mimeType = mime_content_type($file);
                break;
        }
        $stream = fopen($file, 'r');

        $response = new \Zend\Http\Response\Stream();
        $headers = array(
            'Content-type' => $mimeType,
            'Pragma' => 'Public',
        );

        $response->getHeaders()->addHeaders($headers);

        $response->setStream($stream);
        return $response;
    }
}
