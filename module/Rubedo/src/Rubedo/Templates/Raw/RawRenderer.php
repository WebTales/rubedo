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
namespace Rubedo\Templates\Raw;

use Zend\View\Model\ModelInterface;
use Zend\View\Resolver\ResolverInterface;
use Zend\View\Renderer\RendererInterface;
/**
 * Interface class for Zend_View compatible template engine implementations
 */
class RawRenderer implements RendererInterface
{

    /**
     * Return the template engine object, if any
     *
     * If using a third-party template engine, such as Smarty, patTemplate,
     * phplib, etc, return the template engine object. Useful for calling
     * methods on these objects, such as for setting filters, modifiers, etc.
     *
     * @return mixed
     */
    public function getEngine()
    {}

    /**
     * Set the resolver used to map a template name to a resource the renderer may consume.
     *
     * @param ResolverInterface $resolver            
     * @return RendererInterface
     */
    public function setResolver(ResolverInterface $resolver)
    {}

    /**
     * Processes a view script and returns the output.
     *
     * @param string|ModelInterface $nameOrModel
     *            The script/resource process, or a view model
     * @param null|array|\ArrayAccess $values
     *            Values to use during rendering
     * @return string The script output.
     */
    public function render($nameOrModel, $values = null)
    {
        if(!$nameOrModel instanceof RawViewModel){
            return null;
        }
        $model = null;
        
        if ($nameOrModel instanceof ModelInterface) {
            $model = $nameOrModel;
            $template = $model->getTemplate();
            
            if (empty($template)) {
                throw new \Rubedo\Exceptions\Server(sprintf('%s: received View Model argument, but template is empty', __METHOD__));
            }
            
            $values = (array) $model->getVariables();
        }
        $result = array('template'=>$template,'data'=>$values);
        var_dump($result);die();
        return $result;
    }

    /**
     * Can the template be rendered?
     *
     * @param string $name            
     * @return bool
     * @see \ZfcTwig\Twig\Environment::canLoadTemplate()
     */
    public function canRender($name)
    {
        return $this->resolver->resolve($name, $this);
    }
}
