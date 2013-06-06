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
namespace Rubedo\Interfaces\Templates;

/**
 * Front Office Template Service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
Interface IFrontOfficeTemplates
{

    /**
     * render a twig template given an array of data
     * 
     * @param string $template
     *            template name
     * @param array $vars
     *            array of data to be rendered
     * @return string HTML produced by twig
     */
    public function render ($template, array $vars);

    /**
     * return the template directory
     *
     * @return string
     */
    public function getTemplateDir ();

    /**
     * Return the actual path of a twig subpart in the current theme
     *
     * Check if it exist in current theme, return default path if not
     * 
     * @return string
     */
    public function getFileThemePath ($path);

    /**
     * Get the current theme name
     *
     * @return string
     */
    public function getCurrentTheme ();

    /**
     * Set the current theme name
     * 
     * @param string $theme            
     */
    public function setCurrentTheme ($theme);

    public function getAvailableThemes ();
}
