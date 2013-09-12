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
namespace Rubedo\Exceptions;

use Zend\Http\Header\ContentType;
use Zend\Http\Request;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\View\Model\JsonModel;
use Zend\Mvc\View\Http\ExceptionStrategy;
use Zend\Http\Response as HttpResponse;
use Rubedo\Content\Context;
use Rubedo\Services\Manager;
use Zend\Debug\Debug;

/**
 * Handle response as Json if in an asynchroneus context
 *
 * Same as Rubedo 1.x solution but inspired from https://github.com/superdweebie/exception-module for ZF2 handling
 *
 * @author jbourdin
 * @author Tim Roediger <superdweebie@gmail.com>
 */
class JsonExceptionStrategy extends ExceptionStrategy
{

    protected $displayExceptions;

    protected $exceptionMap;

    protected $describePath;

    public function getDisplayExceptions()
    {
        return $this->displayExceptions;
    }

    public function setDisplayExceptions($displayExceptions)
    {
        $this->displayExceptions = $displayExceptions;
    }

    public function getExceptionMap()
    {
        return $this->exceptionMap;
    }

    public function setExceptionMap($exceptionMap)
    {
        $this->exceptionMap = $exceptionMap;
    }

    public function getDescribePath()
    {
        return $this->describePath;
    }

    public function setDescribePath($describePath)
    {
        $this->describePath = $describePath;
    }

    /**
     * Create an exception json view model, and set the HTTP status code
     *
     *
     * @param MvcEvent $e            
     * @return void
     */
    public function prepareExceptionViewModel(MvcEvent $e)
    {
        // Do nothing if no error in the event
        if (! ($error = $e->getError())) {
            return;
        }
        
        // Do nothing if the result is a response object
        $result = $e->getResult();
        if ($result instanceof HttpResponse) {
            return;
        }
        
        if ($error != Application::ERROR_EXCEPTION) {
            return;
        }
        
        if (! $e->getRequest() instanceof Request) {
            return;
        }
        if (! ($exception = $e->getParam('exception'))) {
            return;
        }
        
        $response = $e->getResponse();
        if (! $response) {
            $response = new HttpResponse();
            $e->setResponse($response);
        }
        switch (get_class($exception)) {
            case 'Rubedo\\Exceptions\\User':
                $response->setStatusCode(200);
                break;
            case 'Rubedo\\Exceptions\\NotFound':
                $response->setStatusCode(404);
                break;
            case 'Rubedo\\Exceptions\\Access':
                $response->setStatusCode(403);
                break;
            default:
                $response->setStatusCode(500);
                break;
        }
        
        if ($response->getStatusCode() != 200) {
            $serverParams = $e->getRequest()->getServer();
            $context = array(
                'user' => Manager::getService('CurrentUser')->getCurrentUser(),
                'remote_ip' => $serverParams->get('X-Forwarded-For', $serverParams->get('REMOTE_ADDR')),
                'uri' => $e->getRequest()
                    ->getUri()
                    ->toString(),
                'class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            )
            ;
        }
        
        if ($response->getStatusCode() == 500) {
            $context['errorStack'] = $exception->getTrace();
            Manager::getService('Logger')->error($exception->getMessage(), $context);
        }
        
        if ($response->getStatusCode() == 403) {
            Manager::getService('SecurityLogger')->error($exception->getMessage(), $context);
        }
        
        if (! $e->getRequest()->isXmlHttpRequest() && ! Context::getExpectJson()) {
            return;
        }
        
        $modelData = $this->serializeException($exception);
        $e->setResult(new JsonModel($modelData));
        $e->setError(false);
        
        $response->getHeaders()->addHeaders([
            ContentType::fromString('Content-type: application/json')
        ]);
    }

    public function serializeException($exception)
    {
        $data['success'] = false;
        $data['msg'] = $exception->getMessage();
        if ($this->displayExceptions) {
            $data['class'] = get_class($exception);
            $data['file'] = $exception->getFile();
            $data['line'] = $exception->getLine();
        }
        
        return $data;
    }
}