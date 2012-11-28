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
namespace Rubedo\Templates;

use Rubedo\Interfaces\Templates\IFrontOfficeTemplates;
Use Rubedo\Services\Manager;
/**
 * Front Office URL service
 *
 *
 * @author jbourdin
 * @category Rubedo
 * @package Rubedo
 */
class FrontOfficeTemplates implements  IFrontOfficeTemplates
{

    /**
     * twig environnelent object
     * @var \Twig_Environment
     */
    protected $_twig;

    /**
     * Twig options array
     * @var array
     */
    protected $_options = array();

    /**
     * Directory containing twig templates
     *
     * @var string
     */
    protected static $templateDir = null;

    /**
     * Current theme name
     *
     * @var string
     */
    protected static $_currentTheme = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * initialise Twig Context
     * @param string $lang current language
     */
    protected function _init()
    {

        $this->_options = array('templateDir' => APPLICATION_PATH . "/../data/templates", 'cache' => APPLICATION_PATH . "/../cache/twig", 'debug' => true, 'auto_reload' => true);
        if (isset($this->_service)) {
            $this->_options = $this->_service->getCurrentOptions();
        }

        $lang = Manager::getService('Session')->get('lang', 'fr');

        $loader = new \Twig_Loader_Filesystem($this->_options['templateDir']);
        $this->_twig = new \Twig_Environment($loader, $this->_options);

        $this->_twig->addExtension(new \Twig_Extension_Debug());

        $this->_twig->addExtension(new \Twig_Extension_Translate($lang));

        $this->_twig->addExtension(new \Twig_Extension_Highlight());

        $this->_twig->addExtension(new \Twig_Extension_Intl());
    }

    /**
     * render a twig template given an array of data
     * @param string $template template name
     * @param array $vars array of data to be rendered
     * @return string HTML produced by twig
     */
    public function render($template, array $vars)
    {
        $templateObj = $this->_twig->loadTemplate($template);
        return $templateObj->render($vars);
    }

    /**
     * return the template directory
     *
     * @return string
     */
    public function getTemplateDir()
    {
        if (!isset(self::$templateDir)) {
            $this->_options = array('templateDir' => APPLICATION_PATH . "/../public/themes", 'cache' => APPLICATION_PATH . "/../cache/twig", 'debug' => true, 'auto_reload' => true);
            if (isset($this->_service)) {
                $this->_options = $this->_service->getCurrentOptions();
            }

            self::$templateDir = $this->_options['templateDir'];
        }
        return self::$templateDir;
    }

    /**
     * Return the actual path of a twig subpart in the current theme
     *
     * Check if it exist in current theme, return default path if not
     * @return string
     */
    public function getFileThemePath($path)
    {
        if (is_file($this->getTemplateDir() . '/' . $this->getCurrentTheme() . '/' . $path)) {
            return '/' . $this->getCurrentTheme() . '/' . $path;
        } else {
            return '/default/' . $path;
        }
    }

    /**
     * Get the current theme name
     *
     * @return string
     */

    public function getCurrentTheme()
    {
        if (!isset(self::$templateDir)) {

            self::$_currentTheme = 'default';
        }
        return self::$_currentTheme;
    }

    /**
     * Set the current theme name
     * @param string $theme
     */
    public function setCurrentTheme($theme)
    {
        self::$_currentTheme = $theme;
    }

}
