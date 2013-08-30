<?php

/**
 * Rubedo -- ECM solution Copyright (c) 2013, WebTales
 * (http://www.webtales.fr/). All rights reserved. licensing@webtales.fr
 * Open Source License
 * ------------------------------------------------------------------------------------------
 * Rubedo is licensed under the terms of the Open Source GPL 3.0 license.
 *
 * @category Rubedo
 * @package Rubedo
 * @copyright Copyright (c) 2012-2013 WebTales (http://www.webtales.fr)
 * @license http://www.gnu.org/licenses/gpl.html Open Source GPL 3.0 license
 */
namespace Rubedo\Router;

use Rubedo\Services\Manager;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\TreeRouteStack;

/**
 * Zend_Controller_Router_Route implementation for frontend pages
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FrontofficeRoute implements RouteInterface
{

    /**
     * Default params
     *
     * @var array
     */
    protected $defaults = array();

    /**
     * Matched params
     *
     * @var array
     */
    protected $value = array();
    
    /*
     * (non-PHPdoc) @see \Zend\Mvc\Router\RouteInterface::assemble()
     */
    public function assemble(array $params = array(), array $options = array())
    {
        // @todo assemble for this route
    }
    
    /*
     * (non-PHPdoc) @see \Zend\Mvc\Router\RouteInterface::match()
     */
    public function match(\Zend\Stdlib\RequestInterface $request)
    {
        try {
            if (method_exists($request, 'getUri')) {
                $uri = $request->getUri();
                $pageId = Manager::getService('Url')->getPageId($uri->getPath(), $uri->getHost());
            }
        } catch (\Rubedo\Exceptions\Server $exception) {
            return null;
        }
        if ($pageId === null) {
            return null;
        }
        $this->value['pageId'] = $pageId;
        $params = array();
        $params['controller'] = 'Rubedo\\Frontoffice\\Controller\\Index';
        $params['action'] = 'index';
        $params['pageId'] = $pageId;
        $match = new RouteMatch(array_merge($this->defaults, $params));
        
        return $match;
    }

    /**
     * factory(): defined by RouteInterface interface.
     *
     * @see \Zend\Mvc\Router\RouteInterface::factory()
     * @param array|Traversable $options            
     * @return FrontofficeRoute
     * @throws Exception\InvalidArgumentException
     */
    public static function factory($options = array())
    {
        return new static();
    }
    
    /*
     * (non-PHPdoc) @see \Zend\Mvc\Router\Http\RouteInterface::getAssembledParams()
     */
    public function getAssembledParams()
    {
        // this route may not occur as base route of other part routes, so we
        // don't have to return anything here.
        return array();
    }
}