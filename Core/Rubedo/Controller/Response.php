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

namespace Rubedo\Controller;

/**
 * Response object Use to handle block contents as MVC part
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class Response extends \Zend_Controller_Response_Abstract
{
    /**
     * Set body content
     *
     * If $name is not passed, or is not a string, resets the entire body and
     * sets the 'default' key to $content.
     *
     * If $name is a string, sets the named segment in the body array to
     * $content.
     *
     * @param string $content
     * @param null|string $name
     * @return Zend_Controller_Response_Abstract
     */
    public function setBody($content, $name = null) {
        if ((null === $name) || !is_string($name)) {
            $this->_body = array('default' => (string)$content);
        } else {
            $this->_body[$name] = $content;
        }

        return $this;
    }

}
